<?php

namespace Library\Entity\Swoole;

use Swoole\Coroutine;
use Swoole\Http\Request as SwooleRequest;

/**
 * @method static mixed header(string $name = '')
 * @method static mixed server(string $name = '')
 * @method static mixed request(string $name = '')
 * @method static mixed cookies(string $name = '')
 * @method static mixed get(string $name = '')
 * @method static mixed post(string $name = '')
 * @method static mixed files(string $name = '')
 */
class EntitySwooleRequest
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
    public static function delInstance()
    {
        foreach (static::$instancePool as & $workerInstance) {
           unset($workerInstance);
        }
    }

    /**
     * @param  SwooleRequest $instance
     */
    public static function setInstance($instance)
    {
        $cid = Coroutine::getuid();
        if (!static::$instancePool[EntitySwooleWebSever::getInstance()->worker_id][$cid]) {
            static::$instancePool[EntitySwooleWebSever::getInstance()->worker_id][$cid] = $instance;
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
        $instance = self::$instancePool[EntitySwooleWebSever::getInstance()->worker_id][$cid];

        if (!$instance) {
            return '';
        }

        if (in_array($method, ['header', 'server', 'request', 'cookie', 'get', 'post', 'files'])) {
            if ($args[0]) {
                return $instance->$method[$args[0]] ?? '';
            }
            return $instance->$method;
        } else {
            return '';
        }
    }
}