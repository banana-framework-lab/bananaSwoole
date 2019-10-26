<?php
/**
 * Redis实体类
 * User: zzh
 * Date: 2018/10/10
 */

namespace Library\Entity\Model\Cache;

use Library\Config;
use Library\Entity\Swoole\EntitySwooleWebSever;
use Redis as RedisClient;

class EntityRedis
{
    /**
     * @var RedisClient $instance
     */
    private static $instance;

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    /**
     * 初始化Redis实体对象
     * @param int $port
     * @param int $workerId
     */
    public static function instanceStart(int $port, int $workerId)
    {
        if (!static::$instance[$port][$workerId]) {
            $redisConf = Config::get('app.is_server') ? Config::get('redis.server') : Config::get('redis.local');
            $redisServer = new RedisClient();
            $redisServer->connect($redisConf['host'], $redisConf['port'], 0.0);
            $redisServer->auth($redisConf['auth']);
            $redisServer->select($redisConf['database']);

            static::setInstance($port, $workerId, $redisServer);
        }
    }

    /**
     * Set the application instance.
     *
     * @param int $port
     * @param int $workerId
     * @param  RedisClient $instance
     * @return void
     */
    public static function setInstance(int $port, int $workerId, RedisClient $instance)
    {
        static::$instance[$port][$workerId] = $instance;
    }

    /**
     * 删除mysql单例
     */
    public static function delInstance()
    {
        static::$instance = null;
    }

    /**
     * 返回当前实体类实例
     * @return RedisClient
     */
    public static function getInstance()
    {
        return self::$instance;
    }

    /**
     * @param $method
     * @param $args
     * @return mixed
     * @throws \Exception
     */
    public static function __callStatic($method, $args)
    {
        $workId = EntitySwooleWebSever::getInstance()->worker_id;
        $instance = self::$instance[$workId];

        if (!$instance) {
            throw new \Exception('找不到redis对象');
        }

        return $instance->$method(...$args);
    }
}