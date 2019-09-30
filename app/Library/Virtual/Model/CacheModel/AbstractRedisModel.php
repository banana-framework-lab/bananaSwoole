<?php
/**
 * Redis的Model抽象类
 * User: zzh
 * Date: 2018/10/10
 * Time: 17:28
 */

namespace App\Library\Virtual\Model\CacheModel;

use Redis;

/**
 * @property Redis redis
 */
abstract class AbstractRedisModel
{
    /**
     * @param $name
     * @return Redis
     */
    public function __get($name)
    {
        if ($name === 'redis') {
            return $this->getRedis('local');
        }
        return null;
    }

    /**
     * 获取Redis实例
     * @param $name
     * @return Redis
     */
    function getRedis($name)
    {
        static $redisServer = [];
        if (isset($redisServer[$name])) {
            return $redisServer[$name];
        }
        if (IS_SERVER) {
            $redisConf = REDIS_LIST['server'];
        } else {
            $redisConf = REDIS_LIST[$name];
        }
        $redisServer[$name] = new Redis();
        $redisServer[$name]->connect($redisConf['host'], $redisConf['port'], 0.0);
        $redisServer[$name]->auth($redisConf['auth']);
        $redisServer[$name]->select($redisConf['database']);

        return $redisServer[$name];
    }

    /**
     * clone
     * @throws \Exception
     */
    private function __clone()
    {
        throw new \Exception('不允许克隆');
    }
}