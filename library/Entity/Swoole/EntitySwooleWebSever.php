<?php

namespace Library\Entity\Swoole;

use Library\Config;
use Swoole\Http\Server as SwooleHttpServer;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/9/24
 * Time: 10:59
 */

/**
 * Class EntitySwooleWebSever
 * @package Library\Entity\Swoole
 */
class EntitySwooleWebSever
{
    /**
     * @var SwooleHttpServer $instance
     */
    private static $instance = null;

    /**
     * EntitySwooleWebSever constructor.
     */
    private function __construct()
    {

    }

    /**
     * EntitySwooleWebSever clone.
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
            $webServer = new SwooleHttpServer("0.0.0.0", Config::get('swoole.web.port'));
            static::$instance = $webServer;
        }
    }

    /**
     * @return SwooleHttpServer
     */
    public static function getInstance()
    {
        return self::$instance;
    }
}