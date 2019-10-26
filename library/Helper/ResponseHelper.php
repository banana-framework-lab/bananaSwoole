<?php

namespace Library\Helper;

use Library\Entity\Swoole\EntitySwooleWebSever;
use Swoole\Coroutine;

/**
 * Class ResponseHelper
 * @package Library\Helper
 */
class ResponseHelper
{
    /**
     * @var array $instancePool
     */
    private static $instancePool = [];

    /**
     * ResponseHelper constructor.
     */
    private function __construct()
    {

    }

    /**
     * ResponseHelper clone.
     */
    private function __clone()
    {

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
     * 回收对象
     * @param int $workerId
     */
    public static function delInstance(int $workerId = -1)
    {
        if ($workerId == -1) {
            $cid = Coroutine::getuid();
            $workerId = EntitySwooleWebSever::getInstance()->worker_id;
            unset(static::$instancePool[$workerId][$cid]);
        } else {
            unset(static::$instancePool[$workerId]);
        }
    }

    /**
     * json格式的返回
     * @param array $jsonData
     * @param int $options
     */
    public static function json(array $jsonData = [], int $options = (JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK))
    {
        $cid = Coroutine::getuid();
        $workId = EntitySwooleWebSever::getInstance()->worker_id;
        static::$instancePool[$workId][$cid] = json_encode($jsonData, $options);
    }

    /**
     * 获取当前协程的返回数据
     */
    public static function response()
    {
        $cid = Coroutine::getuid();
        $workerId = EntitySwooleWebSever::getInstance()->worker_id;
        return static::$instancePool[$workerId][$cid] ?? '';
    }
}