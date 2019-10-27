<?php

namespace Library\Entity\Swoole;

use Library\Config;
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

    /**
     * EntitySwooleWebSocketSever constructor.
     */
    private function __construct()
    {

    }

    /**
     * EntitySwooleWebSocketSever clone.
     */
    private function __clone()
    {

    }

    /**
     * 初始化SwooleSever的实体类实体
     */
    public static function instanceStart()
    {
        if (!static::$instance) {
            $webServer = new SwooleWebSocketServer("0.0.0.0", Config::get('swoole.socket.port'));
            static::$instance = $webServer;
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