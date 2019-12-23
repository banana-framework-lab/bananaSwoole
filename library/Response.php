<?php

namespace Library;

use Error;
use Library\Entity\Swoole\EntitySwooleServer;
use Swoole\Coroutine;
use Swoole\Http\Response as SwooleResponse;

/**
 * Class ResponseHelper
 * @package Library
 */
class Response
{
    /**
     * @var array $instancePool
     */
    private static $instancePool = [];

    /**
     * @var array $responsePool
     */
    private static $responsePool = [];

    /**
     * @var array $dumpPool
     */
    private static $dumpPool = [];

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
     * @param Response $instance
     */
    public static function setInstance(SwooleResponse $instance)
    {
        if (EntitySwooleServer::getInstance()) {
            $cid = Coroutine::getuid();
            $workId = EntitySwooleServer::getInstance()->worker_id;
            if (!isset(static::$instancePool[$workId][$cid])) {
                static::$instancePool[$workId][$cid] = $instance;
            }
        }
    }

    /**
     * 获取response对象
     * @return Response
     */
    public static function getInstance()
    {
        $cid = Coroutine::getuid();
        $workId = EntitySwooleServer::getInstance()->worker_id;
        return static::$instancePool[$workId][$cid] ?? null;
    }

    /**
     * 获取整个请求对象
     * @return array
     */
    public static function getResponseInstance()
    {
        return self::$responsePool;
    }

    /**
     * 回收对象
     * @param int $workerId
     */
    public static function delInstance(int $workerId = -1)
    {
        if ($workerId == -1) {
            $cid = Coroutine::getuid();
            $workerId = EntitySwooleServer::getInstance()->worker_id;
            unset(static::$responsePool[$workerId][$cid]);
            unset(static::$instancePool[$workerId][$cid]);
            unset(static::$dumpPool[$workerId][$cid]);
        } else {
            unset(static::$responsePool[$workerId]);
            unset(static::$instancePool[$workerId]);
            unset(static::$dumpPool[$workerId]);
        }
    }

    /**
     * json格式的返回
     * @param array $jsonData
     * @param int $options
     */
    public static function json(array $jsonData = [], int $options = (JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK))
    {
        if (EntitySwooleServer::getInstance()) {
            $cid = Coroutine::getuid();
            $workId = EntitySwooleServer::getInstance()->worker_id;
            static::$responsePool[$workId][$cid] = json_encode($jsonData, $options);
        } else {
            echo json_encode($jsonData, $options);
            exit;
        }
    }

    /**
     * 获取当前协程的返回数据
     */
    public static function response()
    {
        if (EntitySwooleServer::getInstance()) {
            $cid = Coroutine::getuid();
            $workerId = EntitySwooleServer::getInstance()->worker_id;
            return ((static::dumpResponse() ?? "") . (static::$responsePool[$workerId][$cid] ?? ''));
        } else {
            return [];
        }
    }



    /*******************************************************************************************************************/
    /*                                                 var_dump模块
    /*******************************************************************************************************************/

    /**
     * var_dump出去的数据
     * @param mixed $content
     */
    public static function dump($content)
    {
        $cid = Coroutine::getuid();
        $workId = EntitySwooleServer::getInstance()->worker_id;
        static::$dumpPool[$workId][$cid][] = print_r($content, true);
    }

    /**
     * 获取dump的返回值
     * @return string
     */
    public static function dumpResponse()
    {
        $cid = Coroutine::getuid();
        $workerId = EntitySwooleServer::getInstance()->worker_id;
        $dumpData = static::$dumpPool[$workerId][$cid] ?? [];
        $dumpString = '';
        foreach ($dumpData as $key => $value) {
            $dumpString .= $value;
        }
        return $dumpString;
    }

    /**
     * var_dump推出协程
     * @throws Error
     */
    public static function exit()
    {
        throw new Error('exit to get dump data', 888);
    }
}