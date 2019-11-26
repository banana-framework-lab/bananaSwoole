<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/10/31
 * Time: 16:41
 */

namespace Library\Pool;

use Library\Config;
use Library\Entity\Model\DataBase\EntityMysql;
use Swoole\Coroutine\MySQL;

class CoroutineMysqlClientPool
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
     * @throws \Throwable
     */
    public static function poolInit()
    {
        for ($i = 1; $i <= self::$poolSize; $i++) {
            if ($i <= 1) {
                EntityMysql::instanceStart();
            }
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
        self::$pool = [];
        self::$poolSize = 5;
        self::$freeSize = 0;
        self::$busySize = 0;
    }

    /**
     * @return MySQL
     */
    private static function getClient()
    {
        $mysqlConfig = Config::get('mysql.local');
        $coroutineMysqlConfig = [
            'host' => $mysqlConfig['host'],
            'port' => $mysqlConfig['port'] ?? 3306,
            'user' => $mysqlConfig['username'],
            'password' => $mysqlConfig['password'],
            'database' => $mysqlConfig['database'],
        ];
        $client = new MySQL('mysql');
        $client->connect($coroutineMysqlConfig);
        return $client;
    }

    /**
     * 获取连接
     * @return MySQL
     */
    public static function get(): MySQL
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
     * @param MySQL $client
     */
    public static function back(MySQL $client)
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

