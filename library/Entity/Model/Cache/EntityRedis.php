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

/**
 * Class EntityRedis
 * @package Library\Entity\Model\Cache
 *
 * @method static string|bool get(string $key)
 * @method static bool set(string $key, string $value, int $timeout = 0)
 */
class EntityRedis
{
    /**
     * @var RedisClient $instance
     */
    private static $instance;

    /**
     * EntityRedis constructor.
     */
    private function __construct()
    {
    }

    /**
     * EntityRedis clone.
     */
    private function __clone()
    {
    }

    /**
     * 初始化Redis实体对象
     * @param int $workerId
     */
    public static function instanceStart(int $workerId)
    {
        if (!static::$instance[$workerId]) {
            $redisConf = Config::get('app.is_server') ? Config::get('redis.server') : Config::get('redis.local');
            $redisServer = new RedisClient();
            $redisServer->connect($redisConf['host'], $redisConf['port'], 0.0);
            $redisServer->auth($redisConf['auth']);
            $redisServer->select($redisConf['database']);

            self::setInstance($workerId, $redisServer);
        }
    }

    /**
     * 保存Redis实体对象
     * @param int $workerId
     * @param  RedisClient $instance
     * @return void
     */
    private static function setInstance(int $workerId, RedisClient $instance)
    {
        static::$instance[$workerId] = $instance;
    }

    /**
     * 返回当前实体类实例
     * @return RedisClient
     */
    public static function getInstance()
    {
        $workerId = EntitySwooleWebSever::getInstance()->worker_id;
        return self::$instance[$workerId];
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