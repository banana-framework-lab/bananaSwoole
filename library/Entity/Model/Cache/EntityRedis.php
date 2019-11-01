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
     */
    public static function instanceStart()
    {
        if (!static::$instance) {
            $redisConf = Config::get('app.is_server') ? Config::get('redis.server') : Config::get('redis.local');
            $redisServer = new RedisClient();
            $redisServer->connect($redisConf['host'], $redisConf['port'], 0.0);
            $redisServer->auth($redisConf['auth']);
            $redisServer->select($redisConf['database']);

            self::setInstance($redisServer);
        }
    }

    /**
     * 保存Redis实体对象
     * @param  RedisClient $instance
     * @return void
     */
    private static function setInstance(RedisClient $instance)
    {
        static::$instance = $instance;
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
        $instance = self::$instance;

        if (!$instance) {
            throw new \Exception('找不到redis对象');
        }

        return $instance->$method(...$args);
    }
}