<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/11/1
 * Time: 13:56
 */

namespace Library\Entity\Swoole;

use Swoole\Server;

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
     * 初始化EntitySwooleServer的实体类实体
     * @param Server $server
     */
    public static function setInstance(Server $server)
    {
        if (!static::$instance) {
            static::$instance = $server;
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