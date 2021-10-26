<?php

namespace Library\Container\Instance;

use Library\Exception\LogicException;
use Swoole\Http\Request as SwooleHttpRequest;
use Swoole\Http\Response as SwooleHttpResponse;

/**
 * Class Response
 * @package Library
 */
class Response
{
    /**
     * @var SwooleHttpResponse[] $pool
     */
    private $pool = [];

    /**
     * @var array $dumpPool
     */
    private $dumpPool = [];

    /**
     * @param SwooleHttpResponse $instance
     * @param int $workerId
     * @param int $cId
     */
    public function setResponse(SwooleHttpResponse $instance, int $workerId, int $cId)
    {
        if (!isset($this->pool[$workerId][$cId])) {
            $this->pool[$workerId][$cId] = $instance;
        }
    }

    /**
     * 获取指定协程下的对象
     * @param int $workerId
     * @param int $cId
     * @return SwooleHttpRequest
     */
    public function getResponse(int $workerId = 0, int $cId = 0): ?SwooleHttpRequest
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
    /*******************************************************************************************************************/
    /*                                                 var_dump模块
    /*******************************************************************************************************************/

    /**
     * var_dump出去的数据
     * @param mixed $content
     * @param int $workerId
     * @param int $cId
     */
    public function dump($content, int $workerId, int $cId)
    {
        $this->dumpPool[$workerId][$cId][] = print_r($content, true);
    }

    /**
     * 获取dump的返回值
     * @param int $workerId
     * @param int $cId
     * @return string
     */
    public function dumpFlush(int $workerId, int $cId): string
    {
        $dumpData = $this->dumpPool[$workerId][$cId] ?? [];
        return implode('', $dumpData);
    }

    /**
     * var_dump推出协程
     */
    public static function exit()
    {
        throw new LogicException('exit to get dump data', C_EXIT_CODE);
    }
}