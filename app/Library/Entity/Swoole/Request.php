<?php

namespace App\Library\Entity\Swoole;

use Swoole\Coroutine;

/**
 * @method static mixed header(string $name = '')
 * @method static mixed server(string $name = '')
 * @method static mixed request(string $name = '')
 * @method static mixed cookies(string $name = '')
 * @method static mixed get(string $name = '')
 * @method static mixed post(string $name = '')
 * @method static mixed files(string $name = '')
 */
class Request
{
    private static $instancePool = [];

    private function __construct()
    {

    }

    private function __clone()
    {

    }

    /**
     * 重启回收对象
     */
    public static function recoverInstance()
    {
        foreach (static::$instancePool as & $workerInstance) {
           unset($workerInstance);
        }
    }

    /**
     * @param  \Swoole\Http\Request $instance
     * @return void
     */
    public static function setReqInstance($instance)
    {
        $cid = Coroutine::getuid();
        if (!static::$instancePool[HttpSever::getInstance()->worker_id][$cid]) {
            static::$instancePool[HttpSever::getInstance()->worker_id][$cid] = $instance;
        }
    }

    /**
     * @param $method
     * @param $args
     * @return string
     */
    public static function __callStatic($method, $args)
    {
        $cid = Coroutine::getuid();
        $instance = self::$instancePool[HttpSever::getInstance()->worker_id][$cid];

        if (!$instance) {
            return null;
        }

        if (in_array($method, ['header', 'server', 'request', 'cookie', 'get', 'post', 'files'])) {
            if ($args[0]) {
                return $instance->$method[$args[0]] ?? '';
            }
            return $instance->$method;
        } else {
            return null;
        }
    }
}