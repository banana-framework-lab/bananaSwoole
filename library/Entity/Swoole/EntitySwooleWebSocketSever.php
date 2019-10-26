<?php

namespace Library\Entity\Swoole;

use Swoole\WebSocket\Server as SwooleWebSocketServer;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/9/24
 * Time: 10:59
 */

/**
 * Class EntitySwooleWebSocketSever
 * @package Library\Entity\Swoole
 */
class EntitySwooleWebSocketSever
{
    /**
     * @var SwooleWebSocketServer $instance
     */
    private static $instance = null;

    private function __construct()
    {

    }

    private function __clone()
    {

    }

    /**
     * 初始化SwooleSever的实体类实体
     */
    public static function instanceStart()
    {
        if (!static::$instance) {
            $webServer = new SwooleWebSocketServer("0.0.0.0", WEB_SOCKET_SERVER_PORT);
            static::$instance = $webServer;
        }
    }

    /**
     * @param  SwooleWebSocketServer $instance
     * @return void
     */
    public static function setInstance(SwooleWebSocketServer $instance)
    {
        if (!static::$instance) {
            static::$instance = $instance;
        }
    }

    /**
     * @return SwooleWebSocketServer
     */
    public static function getInstance()
    {
        return self::$instance;
    }
}