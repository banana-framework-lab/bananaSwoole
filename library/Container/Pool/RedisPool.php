<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/10/31
 * Time: 16:41
 */

namespace Library\Container\Pool;

use Exception;
use Library\Container;
use Library\Exception\LogicException;
use Redis;
use Swoole\Coroutine\Channel;

class RedisPool
{
    /**
     * Redis连接池
     * @var Channel $pool
     */
    private $pool;

    /**
     * 连接池大小
     * @var int $poolSize
     */
    private $poolSize = 5;

    /**
     * 初始化连接池
     * @param string $configName
     * @throws Exception
     */
    public function __construct(string $configName)
    {
        $poolSize = Container::getConfig()->get('pool.redis.size');
        if (!$poolSize) {
            throw new LogicException('请配置具体redis连接池大小');
        }
        $this->pool = new Channel(
            $poolSize
        );
        for ($i = 1; $i <= $this->poolSize; $i++) {
            $this->pool->push($this->getClient($configName));
        }
    }

    /**
     * 获取
     * @param string $configName
     * @return Redis
     * @throws Exception
     */
    private function getClient(string $configName): Redis
    {
        if (!$configName) {
            $configName = Container::getServerConfigIndex();
        }

        $redisConf = Container::getConfig()->get("redis.{$configName}");

        if ($redisConf) {
            $redisServer = new Redis();
            $redisServer->connect($redisConf['host'], $redisConf['port'], 0.0);
            $redisServer->auth($redisConf['auth']);
            $redisServer->select($redisConf['database']);

            return $redisServer;
        } else {
            throw new Exception('请配置Redis信息');
        }
    }

    /**
     * 获取连接
     * @return Redis
     */
    public function get(): Redis
    {
        return $this->pool->pop();
    }

    /**
     * 归还连接
     * @param Redis $client
     */
    public function back(Redis $client)
    {
        $this->pool->push($client);
    }
}

