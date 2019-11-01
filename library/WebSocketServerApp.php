<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/9/5 0005
 * Time: 17:02
 */

namespace Library;

use Library\Entity\MessageQueue\EntityRabbit;
use Library\Entity\Model\Cache\EntityRedis;
use Library\Entity\Model\DataBase\EntityMongo;
use Library\Entity\Model\DataBase\EntityMysql;
use Library\Virtual\Handler\AbstractHandler;
use Throwable;
use Swoole\Http\Request as SwooleHttpRequest;
use Swoole\WebSocket\Server as SwooleSocketServer;
use Swoole\WebSocket\Frame as SwooleSocketFrame;


/**
 * Class WebSocketServerApp
 * @package Library
 */
class WebSocketServerApp
{
    /**
     * 初始化webSocketApp对象
     * @param int $workerId
     */
    public static function init(int $workerId)
    {
        //开启php调试模式
        if (Config::get('app.debug')) {
            error_reporting(E_ALL);
        }

        try {
            // 配置文件初始化
            Config::instanceStart();

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

            // 消化消息队列的消息
            Message::consume();
        } catch (Throwable $e) {
            echo "worker_id:{$workerId}  启动时报错  " . $e->getMessage() . "\n";
            return;
        }
    }

    /**
     * 用户连接webSocket
     * @param SwooleSocketServer $server
     * @param SwooleHttpRequest $request
     */
    public static function open(SwooleSocketServer $server, SwooleHttpRequest $request)
    {
        $openData = ($request->get ?: []) + ($request->post ?: []);

        // 选出所需通道
        $channelObject = Channel::route($openData);

        //初始化Handler
        $handlerClass = $channelObject->getHandler();

        try {
            // 初始化事件器
            if (class_exists($handlerClass)) {
                /* @var AbstractHandler $handler */
                $handler = new $handlerClass();
                if (method_exists($handlerClass, 'open')) {
                    //fd绑定通道
                    Binder::fdBindChannel($request->fd, $channelObject);
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
    public static function message(SwooleSocketServer $server, SwooleSocketFrame $frame)
    {
        try {
            // 获取所需通道
            $channelObject = Binder::getChannelByFd($frame->fd);
            if (!$channelObject) {
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
    public static function close(SwooleSocketServer $server, int $fd)
    {
        try {
            // 获取所需通道
            $channelObject = Binder::getChannelByFd($fd);
            if (!$channelObject) {
                echo "找不到fd对应的Channel!\n";
                return;
            }

            //初始化Handler
            $handlerClass = $channelObject->getHandler();

            //fd解绑Channel
            Binder::fdUnBindChannel($fd);

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