<?php
/**
 * Redis的Model抽象类
 * User: zzh
 * Date: 2018/10/10
 * Time: 17:28
 */

namespace Library\Virtual\Model\CacheModel;

use Library\Entity\Model\Cache\EntityRedis;
use Redis;

/**
 * Class AbstractRedisModel
 * @property Redis redis
 * @package Library\Virtual\Model\CacheModel
 */
abstract class AbstractRedisModel
{
    /**
     * @param $name
     * @return null|Redis
     */
    public function __get($name)
    {
        if ($name === 'redis') {
            return EntityRedis::getInstance();
        }
        return null;
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