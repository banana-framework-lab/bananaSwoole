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
use Library\Entity\EntityRedisClient;
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
    public function __construct($configName)
    {
        $this->pool = new Channel(
            Container::getConfig()->get('pool.redis.size', 5)
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
    private function getClient($configName = '')
    {
        if (!$configName) {
            $configName = Container::getServerConfigIndex();
        }

        $redisConf = Container::getConfig()->get("redis.{$configName}");

        if ($redisConf) {
            $redisServer = new EntityRedisClient();
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
     * @return EntityRedisClient
     */
    public function get(): EntityRedisClient
    {
        return $this->pool->pop();
    }

    /**
     * 归还连接
     * @param EntityRedisClient $client
     */
    public function back(EntityRedisClient $client)
    {
        $this->pool->push($client);
    }
}

