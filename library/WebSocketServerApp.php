<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/9/5 0005
 * Time: 17:02
 */

namespace Library;

use Library\Entity\MessageQueue\EntitySwooleRabbit;
use Library\Entity\Model\Cache\EntityRedis;
use Library\Entity\Model\DataBase\EntityMongo;
use Library\Entity\Model\DataBase\EntityMysql;
use Library\Entity\MessageQueue\EntityRabbit;
use Library\Expection\WebException;
use Library\Helper\RequestHelper;
use Library\Helper\ResponseHelper;
use Library\Object\ChannelObject;
use Library\Object\RouteObject;
use Library\Pool\CoroutineMysqlClientPool;
use Library\Pool\CoroutineRedisClientPool;
use Library\Virtual\Handler\AbstractHandler;
use Library\Virtual\Middle\AbstractMiddleWare;
use Swoole\Http\Request as SwooleHttpRequest;
use Swoole\Table;
use Swoole\WebSocket\Server as SwooleSocketServer;
use Swoole\WebSocket\Frame as SwooleSocketFrame;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;
use Throwable;


/**
 * Class WebSocketServerApp
 * @package Library
 */
class WebSocketServerApp
{
    public $table;

    public function __construct(Table $table)
    {
        $this->table = $table;
    }

    /**
     * 初始化webSocketApp对象
     * @param SwooleSocketServer $server
     * @param int $workerId
     * @return bool
     */
    public function init(SwooleSocketServer $server, int $workerId): bool
    {
        try {
            // 通道配置
            Channel::instanceStart();

            // mysql数据库初始化
            EntityMysql::instanceStart();

            // mongo数据库初始化
            EntityMongo::instanceStart();

            // Redis缓存初始化
            EntityRedis::instanceStart();

            // rabbitMq初始化
            EntityRabbit::instanceStart();
            EntitySwooleRabbit::instanceStart();

            // 协程mysql连接池初始化
            CoroutineMysqlClientPool::poolInit();

            // 协程redis连接池初始化
            CoroutineRedisClientPool::poolInit();

            // 消化消息队列的消息
            Message::consume();

            //开启php调试模式
            if (Config::get('app.debug')) {
                error_reporting(E_ALL);
            }
            return true;
        } catch (Throwable $e) {
            echo "///////////      worker_id:{$workerId}  启动时报错  " . $e->getMessage() . "\n";
            $server->shutdown();
            return false;
        }
    }

    /**
     * 用户连接webSocket
     * @param SwooleSocketServer $server
     * @param SwooleHttpRequest $request
     */
    public function open(SwooleSocketServer $server, SwooleHttpRequest $request)
    {
        $openData = ($request->get ?: []) + ($request->post ?: []);

        // 选出所需通道
        $channelObject = Channel::route($openData);

        //过滤错误的连接
        if (!$channelObject->getChannel()) {
            $server->disconnect($request->fd, 1000, "找不到fd对应的Channel");
            return;
        }

        //初始化Handler
        $handlerClass = $channelObject->getHandler();

        try {
            // 初始化事件器
            if (class_exists($handlerClass)) {
                /* @var AbstractHandler $handler */
                $handler = new $handlerClass();
                if (method_exists($handlerClass, 'open')) {
                    //fd绑定通道
                    $this->table->set($request->fd, $channelObject->toArray());

                    //fd打开事件
                    $handler->open($server, $request);
                } else {
                    if (Config::get('app.debug')) {
                        $server->disconnect($request->fd, 1000, "找不到open方法");
                    } else {
                        $server->disconnect($request->fd, 1000, "close");
                    }
                }
            } else {
                if (Config::get('app.debug')) {
                    $server->disconnect($request->fd, 1000, "找不到{$handlerClass}");
                } else {
                    $server->disconnect($request->fd, 1000, "close!");
                }
            }
        } catch (Throwable $e) {
            if (Config::get('app.debug')) {
                echo $e->getMessage() . "\n" . $e->getTraceAsString();
                $server->disconnect($request->fd, 1000, "已断开连接.");
            } else {
                $server->disconnect($request->fd, 1000, "close.");
            }
        }
    }

    /**
     * 收到消息
     * @param SwooleSocketServer $server
     * @param SwooleSocketFrame $frame
     */
    public function message(SwooleSocketServer $server, SwooleSocketFrame $frame)
    {
        $tableData = $this->table->get($frame->fd);
        try {
            // 获取所需通道
            $channelObject = new ChannelObject();
            $channelObject->setChannel($tableData['channel']);
            $channelObject->setHandler($tableData['handler']);

            if (!$channelObject->getChannel()) {
                $server->disconnect($frame->fd, 1000, "找不到fd对应的Channel");
                return;
            }

            //初始化Handler
            $handlerClass = $channelObject->getHandler();

            // 初始化事件器
            if (class_exists($handlerClass)) {
                /* @var AbstractHandler $handler */
                $handler = new $handlerClass();
                if (method_exists($handlerClass, 'open')) {
                    $handler->message($server, $frame);
                } else {
                    if (Config::get('app.debug')) {
                        $server->disconnect($frame->fd, 1000, "找不到message方法");
                    } else {
                        $server->disconnect($frame->fd, 1000, "已断开连接");
                    }
                }
            } else {
                if (Config::get('app.debug')) {
                    $server->disconnect($frame->fd, 1000, "找不到{$handlerClass}");
                } else {
                    $server->disconnect($frame->fd, 1000, "已断开连接!");
                }
            }
        } catch (Throwable $e) {
            if (Config::get('app.debug')) {
                echo $e->getMessage() . "\n" . $e->getTraceAsString();
                $server->disconnect($frame->fd, 1000, "已断开连接.");
            } else {
                $server->disconnect($frame->fd, 1000, "已断开连接.");
            }
        }
    }

    /**
     * 关闭webSocket连接
     * @param SwooleSocketServer $server
     * @param int $fd
     */
    public function close(SwooleSocketServer $server, int $fd)
    {
        $tableData = $this->table->get($fd) ?: [];
        if ($tableData['http'] == 1) {
            $this->table->del($fd);
            return;
        } else {
            try {
                // 获取所需通道
                $channelObject = new ChannelObject();
                $channelObject->setChannel($tableData['channel']);
                $channelObject->setHandler($tableData['handler']);
                if (!$channelObject) {
                    echo "{$fd}找不到fd对应的Channel!\n";
                    return;
                }

                //初始化Handler
                $handlerClass = $channelObject->getHandler();

                //fd解绑Channel
                $this->table->del($fd);

                // 初始化事件器
                if (class_exists($handlerClass)) {
                    /* @var AbstractHandler $handler */
                    $handler = new $handlerClass();
                    if (method_exists($handlerClass, 'open')) {
                        $handler->close($server, $fd);
                    } else {
                        echo "找不到close方法\n";
                    }
                } else {
                    echo "找不到{$handlerClass}\n";
                }
            } catch (Throwable $e) {
                echo $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
            }
        }
    }

    /**
     * onRequest执行入口
     * @param SwooleRequest $request
     * @param SwooleResponse $response
     */
    public function run(SwooleRequest $request, SwooleResponse $response)
    {
        //标识此次fd为http请求;
        $this->table->set($request->fd, ['http' => 1]);

        //初始化请求实体类
        RequestHelper::setInstance($request);

        /* @var RouteObject $routeObject */
        $routeObject = Router::router($request->server['request_uri']);

        //初始化方法
        $methodName = $routeObject->getMethod();
        $controllerClass = $routeObject->getController();

        //初始化请求数据
        $getData = $request->get ?: [];
        $postData = $request->post ?: [];
        $rawContentData = json_decode($request->rawContent(), true) ?: [];
        $requestData = array_merge($getData, $postData, $rawContentData);

        //初始化请求中间件
        try {
            $middleClass = str_replace("Controller", "Middle", $controllerClass);;
            /* @var AbstractMiddleWare $middleWare */
            if (method_exists($middleClass, $methodName)) {
                $middleWare = new $middleClass($requestData);
                $middleWare->$methodName();
                $requestData = $middleWare->takeMiddleData();
            }
        } catch (Throwable $e) {
            ResponseHelper::json(['code' => 10000, 'message' => $e->getMessage()]);
            $response->status(200);
            $response->end(ResponseHelper::response());
            return;
        }
        try {
            //初始化控制器
            if (class_exists($controllerClass)) {
                $controller = new $controllerClass($requestData);
                if (method_exists($controller, $methodName)) {
                    $returnData = $controller->$methodName();
                    if ($returnData) {
                        ResponseHelper::json($returnData);
                    }
                } else {
                    if (Config::get('app.debug')) {
                        ResponseHelper::json(['code' => 10000, 'msg' => "找不到{$methodName}"]);
                    } else {
                        $response->status(404);
                        $response->end();
                        return;
                    }
                }
            } else {
                if (Config::get('app.debug')) {
                    ResponseHelper::json(['msg' => "找不到{$controllerClass}"]);
                } else {
                    $response->status(404);
                    $response->end();
                    return;
                }
            }
        } catch (WebException $webE) {
            ResponseHelper::json([
                'code' => $webE->getCode(),
                'msg' => $webE->getMessage()
            ]);
        } catch (Throwable $e) {
            if (Config::get('app.debug')) {
                if ($e->getCode() != 888) {
                    $response->status(200);
                    $response->end($e->getMessage() . "\n" . $e->getTraceAsString());
                } else {
                    $response->status(200);
                    $response->end(ResponseHelper::dumpResponse());
                }
            } else {
                $response->status(500);
                $response->end();
            }
            return;
        }

        // 支持跨域访问
        $response->status(200);
        $response->end(ResponseHelper::response());
    }

}