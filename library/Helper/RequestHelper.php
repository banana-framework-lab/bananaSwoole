<?php

namespace Library\Helper;

use Library\Entity\Swoole\EntitySwooleServer;
use Swoole\Coroutine;
use Swoole\Http\Request as SwooleRequest;

/**
 * Class RequestHelper
 * @package Library\Helper
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
    /**
     * @var array $instancePool
     */
    private static $instancePool = [];

    /**
     * RequestHelper constructor.
     */
    private function __construct()
    {

    }

    /**
     * RequestHelper clone.
     */
    private function __clone()
    {

    }

    /**
     * 保存SwooleRequest请求对象
     * @param  SwooleRequest $instance
     */
    public static function setInstance(SwooleRequest $instance)
    {
        $cid = Coroutine::getuid();
        $workId = EntitySwooleServer::getInstance()->worker_id;
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
        return static::$instancePool;
    }

    /**
     * 回收对象
     * @param int $workId
     */
    public static function delInstance(int $workId = -1)
    {
        if ($workId == -1) {
            $cid = Coroutine::getuid();
            $workId = EntitySwooleServer::getInstance()->worker_id;
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
        $instance = static::$instancePool[EntitySwooleServer::getInstance()->worker_id][$cid] ?? null;

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