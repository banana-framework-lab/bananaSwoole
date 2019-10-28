<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/9/5 0005
 * Time: 17:02
 */

namespace Library;

use Library\Entity\Model\Cache\EntityRedis;
use Library\Entity\Model\DataBase\EntityMongo;
use Library\Entity\Model\DataBase\EntityMysql;
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

            // mysql数据库初始化
            EntityMysql::instanceStart($workerId);

            // mongo数据库初始化
            EntityMongo::instanceStart($workerId);

            // Redis缓存初始化
            EntityRedis::instanceStart($workerId);
        } catch (Throwable $e) {
            echo "worker_id:{$workerId}  启动时报错  " . $e->getMessage() . "\n";
            return;
        }
    }

    /**
     * 连接webSocket客户端
     * @param SwooleSocketServer $server
     * @param SwooleHttpRequest $request
     */
    public static function open(SwooleSocketServer $server, SwooleHttpRequest $request)
    {
        $openData = ($request->get) + ($request->post);

        // 选出所需通道
        $channelObject = Channel::route($openData);

        //初始化Event
        $eventClass = $channelObject->getEvent();

        try {
            // 初始化事件器
            if (class_exists($eventClass)) {
                $event = new $eventClass();
                if (method_exists($event, 'open')) {
                    $event->open($server, $request);
                } else {
                    if (Config::get('app.debug')) {
                        $server->disconnect($request->fd, 1000, "找不到open方法");
                    } else {
                        $server->disconnect($request->fd, 1000, "已断开连接");
                    }
                }
            } else {
                if (Config::get('app.debug')) {
                    $server->disconnect($request->fd, 1000, "找不到{$eventClass}");
                } else {
                    $server->disconnect($request->fd, 1000, "已断开连接!");
                }
            }
        } catch (Throwable $e) {
            if (Config::get('app.debug')) {
                $server->disconnect($request->fd, 1000, $e->getMessage() . "\n" . $e->getTraceAsString());
            } else {
                $server->disconnect($request->fd, 1000, "已断开连接.");
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

    }

    /**
     * 关闭连接
     * @param SwooleSocketServer $server
     * @param int $fd
     */
    public static function close(SwooleSocketServer $server, int $fd)
    {

    }

}