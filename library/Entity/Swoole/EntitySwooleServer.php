<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/11/1
 * Time: 13:56
 */

namespace Library\Entity\Swoole;

use Library\Config;
use Swoole\Server;
use Swoole\WebSocket\Server as SwooleWebSocketServer;

class EntitySwooleServer
{
    /**
     * @var Server $instance
     */
    private static $instance = null;

    /**
     * EntitySwooleServer constructor.
     */
    private function __construct()
    {

    }

    /**
     * EntitySwooleServer clone.
     */
    private function __clone()
    {

    }

    /**
     * 初始化SwooleSever的实体类实体
     * @param string $serverConfigIndex
     */
    public static function instanceStart(string $serverConfigIndex)
    {
        if (!static::$instance) {
            $webServer = new SwooleWebSocketServer("0.0.0.0", Config::get("swoole.{$serverConfigIndex}.port"));
            static::$instance = $webServer;
        }
    }

    /**
     * @return Server
     */
    public static function getInstance(): Server
    {
        return self::$instance;
    }
}