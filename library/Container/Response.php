<?php

namespace Library\Container;

use Swoole\Http\Request as SwooleHttpRequest;
use Swoole\Http\Response as SwooleHttpResponse;

/**
 * Class Response
 * @package Library
 */
class Response
{
    /**
     * @var array $pool
     */
    private $pool = [];

    /**
     * @param SwooleHttpResponse | array $instance
     * @param int $workerId
     * @param int $cId
     */
    public function setResponse($instance, int $workerId, int $cId)
    {
        if (!isset($this->pool[$workerId][$cId])) {
            $this->pool[$workerId][$cId] = $instance;
        }
    }

    /**
     * 获取指定协程下的对象
     * @param int $workerId
     * @param int $cId
     * @return SwooleHttpRequest ｜ array
     */
    public function getResponse(int $workerId = 0, int $cId = 0)
    {
        return $this->pool[$workerId][$cId] ?? null;
    }

    /**
     * 回收对象
     * @param int $workerId
     * @param int $cId
     */
    public function delResponse(int $workerId = 0, int $cId = 0)
    {
        unset($this->pool[$workerId][$cId]);
    }

//    /**
//     * json格式的返回
//     * @param array $jsonData
//     * @param int $options
//     */
//    public static function json(array $jsonData = [], int $options = (JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK))
//    {
//        if (EntitySwooleServer::getInstance()) {
//            $cid = Coroutine::getuid();
//            $workId = EntitySwooleServer::getInstance()->worker_id;
//            static::$responsePool[$workId][$cid] = json_encode($jsonData, $options);
//        } else {
//            echo json_encode($jsonData, $options);
//            exit;
//        }
//    }
//
//    /**
//     * 获取当前协程的返回数据
//     */
//    public static function response()
//    {
//        if (EntitySwooleServer::getInstance()) {
//            $cid = Coroutine::getuid();
//            $workerId = EntitySwooleServer::getInstance()->worker_id;
//            return ((static::dumpResponse() ?? "") . (static::$responsePool[$workerId][$cid] ?? ''));
//        } else {
//            return [];
//        }
//    }
//
//
//
//    /*******************************************************************************************************************/
//    /*                                                 var_dump模块
//    /*******************************************************************************************************************/
//
//    /**
//     * var_dump出去的数据
//     * @param mixed $content
//     */
//    public static function dump($content)
//    {
//        $cid = Coroutine::getuid();
//        $workId = EntitySwooleServer::getInstance()->worker_id;
//        static::$dumpPool[$workId][$cid][] = print_r($content, true);
//    }
//
//    /**
//     * 获取dump的返回值
//     * @return string
//     */
//    public static function dumpResponse()
//    {
//        $cid = Coroutine::getuid();
//        $workerId = EntitySwooleServer::getInstance()->worker_id;
//        $dumpData = static::$dumpPool[$workerId][$cid] ?? [];
//        $dumpString = '';
//        foreach ($dumpData as $key => $value) {
//            $dumpString .= $value;
//        }
//        return $dumpString;
//    }
//
//    /**
//     * var_dump推出协程
//     * @throws Error
//     */
//    public static function exit()
//    {
//        throw new Error('exit to get dump data', 888);
//    }
}