<?php

namespace Library\Helper;

use Library\Entity\Swoole\EntitySwooleWebSever;
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
class RequestHelper
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
    public static function setInstance(SwooleRequest $instance)
    {
        $cid = Coroutine::getuid();
        $workId = EntitySwooleWebSever::getInstance()->worker_id;
        if (!isset(static::$instancePool[$workId][$cid])) {
            static::$instancePool[$workId][$cid] = $instance;
        }
    }

    /**
     * 获取整个请求对象
     * @return array
     */
    public static function getInstance()
    {
        return self::$instancePool;
    }

    /**
     * 回收指定协程内的对象
     * @param int $workId
     */
    public static function recoverInstance(int $workId = -1)
    {
        if ($workId == -1) {
            $cid = Coroutine::getuid();
            $workId = EntitySwooleWebSever::getInstance()->worker_id;
            unset(static::$instancePool[$workId][$cid]);
        } else {
            unset(static::$instancePool[$workId]);
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