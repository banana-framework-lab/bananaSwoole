<?php

namespace App\Library\Entity\Swoole;

use Swoole\Http\Server;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/9/24
 * Time: 10:59
 */
class HttpSever
{
    private static $instance = null;

    private function __construct()
    {

    }

    private function __clone()
    {

    }

    /**
     * @param  Server $instance
     * @return void
     */
    public static function setHttpServerInstance($instance)
    {
        if (!static::$instance) {
            static::$instance = $instance;
        }
    }

    /**
     * @return Server
     */
    public static function getInstance()
    {
        return self::$instance;
    }
}