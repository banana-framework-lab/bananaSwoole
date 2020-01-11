<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/9/5 0005
 * Time: 17:02
 */

namespace Library\App\Server;

use Library\Channel;
use Library\Config;
use Library\Exception\WebException;
use Library\Object\ChannelObject;
use Library\Object\RouteObject;
use Library\Pool\CoroutineMysqlClientPool;
use Library\Response;
use Library\Router;
use Library\Virtual\Controller\AbstractController;
use Library\Virtual\Handler\AbstractHandler;
use Library\Virtual\MiddleWare\AbstractMiddleWare;
use Library\Virtual\Server\AbstractSwooleServer;
use Swoole\Http\Request as SwooleHttpRequest;
use Swoole\Server\Task;
use Swoole\WebSocket\Server as SwooleSocketServer;
use Swoole\WebSocket\Frame as SwooleSocketFrame;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;
use Throwable;


/**
 * Class DefaultWebSocketServer
 * @package Library
 */
class DefaultSwooleServer extends AbstractSwooleServer
{
    /**
     * 初始化webSocketApp对象
     * @param SwooleSocketServer $server
     * @param int $workerId
     * @return bool
     */
    public function start(SwooleSocketServer $server, int $workerId): bool
    {
        try {
            // 通道配置
            Channel::instanceStart();

            // 路由配置
            Router::instanceStart();

            // 初始化mysql连接池
            CoroutineMysqlClientPool::poolInit();

            // 开启php调试模式
            if (Config::get('app.debug', true)) {
                error_reporting(E_ALL);
            }

            return true;
        } catch (Throwable $e) {
            echo "XXXXXXXXXXX      worker_id:{$workerId}  启动时报错  \n" . $e->getMessage() . "\n";

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

        // 过滤错误的连接
        if (!$channelObject->getChannel()) {
            $server->disconnect(
                $request->fd,
                Config::get('response.code.no_channel', 10000),
                "找不到fd对应的Channel"
            );
            return;
        }

        // open实体方法
        try {
            $handlerClass = $channelObject->getHandler();
            // 初始化Handler
            if (class_exists($handlerClass)) {
                /* @var AbstractHandler $handler */
                $handler = new $handlerClass();
                if (method_exists($handlerClass, 'open')) {
                    // fd绑定通道
                    $this->bindTable->set($request->fd, $channelObject->toArray());
                    // fd打开事件
                    $handler->open($server, $request);
                } else {
                    $server->disconnect(
                        $request->fd,
                        Config::get('response.code.no_open_function', 10001),
                        Config::get('app.debug', true) ? "找不到open方法" : '已断开连接！'
                    );
                }
            } else {
                $server->disconnect(
                    $request->fd,
                    Config::get('response.code.no_handler_class', 10002),
                    Config::get('app.debug', true) ? "找不到{$handlerClass}" : '已断开连接.'
                );
            }
        } catch (Throwable $e) {
            echo $e->getMessage() . "\n" . $e->getTraceAsString();
            $server->disconnect(
                $request->fd,
                Config::get('response.code.fatal_error', 10003),
                "已断开连接."
            );
        }
    }

    /**
     * 收到消息
     * @param SwooleSocketServer $server
     * @param SwooleSocketFrame $frame
     */
    public function message(SwooleSocketServer $server, SwooleSocketFrame $frame)
    {
        $tableData = $this->bindTable->get($frame->fd);
        try {
            // 获取所需通道
            $channelObject = new ChannelObject();
            $channelObject->setChannel($tableData['channel']);
            $channelObject->setHandler($tableData['handler']);

            if (!$channelObject->getChannel()) {
                $server->disconnect(
                    $frame->fd,
                    Config::get('response.code.no_channel', 10000),
                    "找不到fd对应的Channel"
                );
                return;
            }

            // 初始化Handler
            $handlerClass = $channelObject->getHandler();

            // 初始化事件器
            if (class_exists($handlerClass)) {
                /* @var AbstractHandler $handler */
                $handler = new $handlerClass();
                if (method_exists($handlerClass, 'open')) {
                    $handler->message($server, $frame);
                } else {
                    $server->disconnect(
                        $frame->fd,
                        Config::get('response.code.no_message_function', 10004),
                        Config::get('app.debug', true) ? "找不到message方法" : "已断开连接"
                    );
                }
            } else {
                $server->disconnect(
                    $frame->fd,
                    Config::get('response.code.no_handler_class', 10002),
                    Config::get('app.debug', true) ? "找不到{$handlerClass}" : "已断开连接!"
                );
            }
        } catch (Throwable $e) {
            echo $e->getMessage() . "\n" . $e->getTraceAsString();
            $server->disconnect(
                $frame->fd,
                Config::get('response.code.fatal_error', 10003),
                "已断开连接."
            );
        }
    }

    /**
     * 关闭webSocket连接
     * @param SwooleSocketServer $server
     * @param int $fd
     */
    public function close(SwooleSocketServer $server, int $fd)
    {
        $tableData = $this->bindTable->get($fd) ?: [];
        if (!isset($tableData['http'])) {
            $this->bindTable->del($fd);
            return;
        }
        if ($tableData['http'] == 1) {
            $this->bindTable->del($fd);
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
                // 初始化Handler
                $handlerClass = $channelObject->getHandler();

                // fd解绑Channel
                $this->bindTable->del($fd);

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
    public function request(SwooleRequest $request, SwooleResponse $response)
    {
        // 标识此次fd为http请求;
        $this->bindTable->set($request->fd, ['http' => 1]);

        /* @var RouteObject $routeObject */
        $routeObject = Router::router($request->server['request_uri']);

        // 初始化方法
        $methodName = $routeObject->getMethod();
        $controllerClass = $routeObject->getController();

        // 初始化PHPSESSID
        if (in_array($routeObject->getProject(), Config::get('app.session_project', []))) {
            $this->openSession($request, $response);
        }

        // 初始化请求数据
        $getData = $request->get ?: [];
        $postData = $request->post ?: [];
        $rawContentData = json_decode($request->rawContent(), true) ?: [];
        $requestData = array_merge($getData, $postData, $rawContentData);

        // 初始化请求中间件
        try {
            $middleClass = str_replace("Controller", "Middle", $controllerClass);;

            /* @var AbstractMiddleWare $middleWare */
            if (method_exists($middleClass, $methodName)) {
                $middleWare = new $middleClass($requestData);
                $middleWare->$methodName();
                $requestData = $middleWare->takeMiddleData();
            }

        } catch (WebException $webE) {
            Response::json([
                'status' => $webE->getStatus(),
                'code' => $webE->getCode(),
                'message' => $webE->getMessage()
            ]);
        } catch (Throwable $e) {
            Response::json([
                'status' => Config::get('response.status.http_fail', 10001),
                'code' => Config::get('response.code.middleware_error', 10006),
                'message' => $e->getMessage()
            ]);
            $response->status(200);
            $response->end(Response::response());
            return;
        }

        // 初始化控制器
        try {
            if (class_exists($controllerClass)) {

                /* @var AbstractController $controller */
                $controller = new $controllerClass($requestData);
                if (method_exists($controller, $methodName)) {
                    $returnData = $controller->$methodName();
                    if (!empty($returnData)) {
                        Response::json($returnData);
                    }
                } else {
                    if (Config::get('app.debug', true)) {
                        Response::json([
                            'status' => Config::get('response.status.http_fail', 10001),
                            'code' => Config::get('response.code.no_controller_function', 10009),
                            'message' => "找不到{$methodName}"
                        ]);
                    } else {
                        $response->status(404);
                        $response->end();
                        return;
                    }
                }
            } else {
                if (Config::get('app.debug', true)) {
                    Response::json([
                        'status' => Config::get('response.status.http_fail', 10001),
                        'code' => Config::get('response.code.no_controller', 10007),
                        'message' => "找不到{$controllerClass}"
                    ]);
                } else {
                    $response->status(404);
                    $response->end();
                    return;
                }
            }
        } catch (WebException $webE) {
            Response::json([
                'status' => $webE->getStatus(),
                'code' => $webE->getCode(),
                'message' => $webE->getMessage()
            ]);
        } catch (Throwable $e) {
            if (Config::get('app.debug', true)) {
                if ($e->getCode() != 888) {
                    $response->status(200);
                    $response->end($e->getMessage() . "\n" . $e->getTraceAsString());
                } else {
                    $response->status(200);
                    $response->end(Response::dumpResponse());
                }
            } else {
                echo $e->getMessage() . "\n" . $e->getTraceAsString();
                $response->status(500);
                $response->end();
            }
            return;
        }

        // 支持跨域访问
        $response->status(200);
        $response->end(Response::response());
    }

    /**
     * onWorkerExit
     * @param SwooleSocketServer $server
     * @param int $workerId
     */
    public function exit(SwooleSocketServer $server, int $workerId)
    {

    }

    /**
     * onTask事件
     * @param SwooleSocketServer $server
     * @param Task $task
     * @return mixed
     */
    public function task(SwooleSocketServer $server, Task $task)
    {
        return null;
    }
}