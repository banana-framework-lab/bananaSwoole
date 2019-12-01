<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/10/31
 * Time: 16:41
 */

namespace Library\Pool;

use Library\Config;
use Swoole\Coroutine\Redis;

class CoroutineRedisClientPool
{
    /**
     * @var array $pool
     */
    private static $pool;

    /**
     * 连接池大小
     * @var int $poolSize
     */
    private static $poolSize = 5;

    /**
     * 空闲连接
     * @var int $freeSize
     */
    private static $freeSize = 0;

    /**
     * 繁忙连接
     * @var int $busySize
     */
    private static $busySize = 0;

    /**
     * 初始化连接池
     */
    public static function poolInit()
    {
        self::$poolSize = Config::get('pool.redis.size', 5);
        for ($i = 1; $i <= self::$poolSize; $i++) {
            $client = self::getClient();
            self::$pool[] = $client;
            ++self::$freeSize;
        }
    }

    /**
     * 释放连接池
     */
    public static function poolFree()
    {
        self::$pool[] = [];
        self::$poolSize = 5;
        self::$freeSize = 0;
        self::$busySize = 0;
    }

    /**
     * @return Redis
     */
    private static function getClient()
    {
        $redisConfig = Config::get('app.is_server') ? Config::get('redis.server') : Config::get('redis.local');
        $client = new Redis();
        $client->connect($redisConfig['host'], $redisConfig['port']);
        return $client;
    }

    /**
     * 获取连接
     * @return Redis
     */
    public static function get(): Redis
    {
        $client = array_pop(self::$pool);
        if (!$client || self::$freeSize <= 0) {
            self::$pool[] = self::getClient();
            ++self::$poolSize;
            ++self::$freeSize;
            $client = array_pop(self::$pool);
        }
        --self::$freeSize;
        ++self::$busySize;

        return $client;
    }

    /**
     * 归还连接
     * @param Redis $client
     */
    public static function back(Redis $client)
    {
        array_push(self::$pool, $client);
        --self::$busySize;
        ++self::$freeSize;
    }

    /**
     * 返回当前连接池的配置
     * @return array
     */
    public static function getPoolInfo(): array
    {
        return [
            'pool_size' => self::$poolSize,
            'free_size' => self::$freeSize,
            'busy_size' => self::$busySize
        ];
    }
}

