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
use Library\Helper\ResponseHelper;
use Library\Object\WebSocket\SocketGetDataObject;
use Library\Object\WebSocket\SocketUserObject;
use Throwable;
use Swoole\Http\Request as SwooleHttpRequest;
use Swoole\WebSocket\Frame as SwooleSocketFrame;
use Swoole\WebSocket\Server as SwooleSocketServer;


/**
 * Class WebSocketServerApp
 * @package Library
 */
class WebSocketServerApp
{
    /**
     * @param $workerId
     */
    public static function init($workerId)
    {
        //开启php调试模式
        if (Config::get('app.debug')) {
            error_reporting(E_ALL);
        }

        try {
            // 配置文件初始化
            Config::instanceStart();

            // Validate初始化
            Validate::instanceStart();

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
     * @param SwooleSocketServer $server
     * @param SwooleHttpRequest $request
     */
    public static function open(SwooleSocketServer $server, SwooleHttpRequest $request)
    {
        $getData = new SocketGetDataObject($request->get);

        // 检查用户连接socket时的参数
        if (!Validate::checkSocketOpen($getData)) {
            $server->disconnect($getData->fd, 1000, '传参错误');
        }

        // 检查用户连接socket是的加密参数
        if (!Validate::checkSocketOpenSecret($getData)) {
            $server->disconnect($getData->fd, 1000, '校验秘钥错误');
        }

        // 组装用户数据
        $userData = new SocketUserObject($getData->id, $getData->appId, $getData->username, $getData->fd);

        // 选出所需通道
        $eventClass = Channel::route($getData);

        // 初始化事件器
        if (class_exists($eventClass)) {
            $event = new $eventClass($getData, $userData);
            if (method_exists($event, $getData->event)) {
                $event->{$getData->event}();
            } else {
                $server->disconnect($getData->fd, 1000, "找不到{$getData->event}");
            }
        } else {
            $server->disconnect($getData->fd, 1000, "找不到{$eventClass}");
        }
    }

    /**
     *
     */
    public static function message()
    {

    }

    /**
     *
     */
    public static function close()
    {

    }

}