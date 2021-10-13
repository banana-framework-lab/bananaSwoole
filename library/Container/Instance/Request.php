<?php

namespace Library\Container\Instance;

use Swoole\Http\Request as SwooleHttpRequest;

/**
 * Class Request
 * @package Library\Container
 */
class Request
{
    /**
     * @var array $pool
     */
    private $pool = [];

    /**
     * 保存SwooleRequest或者fpm的request请求对象
     * @param SwooleHttpRequest | array $instance
     * @param int $workerId
     * @param int $cId
     */
    public function setRequest($instance, int $workerId = 0, int $cId = 0)
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
    public function getRequest(int $workerId = 0, int $cId = 0)
    {
        return $this->pool[$workerId][$cId] ?? null;
    }

    /**
     * 回收对象
     * @param int $workerId
     * @param int $cId
     */
    public function delRequest(int $workerId = 0, int $cId = 0)
    {
        unset($this->pool[$workerId][$cId]);
    }
}