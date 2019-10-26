<?php
/**
 * MongoDB实体类
 * User: zzh
 * Date: 2018/10/10
 */

namespace Library\Entity\Model\DataBase;

use Library\Config;
use Library\Entity\Swoole\EntitySwooleWebSever;
use MongoDB\Client as MongoDbClient;

class EntityMongo
{
    /**
     * @var MongoDbClient $instance
     */
    private static $instance;

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    /**
     * 初始化Mongo实体对象
     * @param int $port
     * @param int $workerId
     */
    public static function instanceStart(int $port, int $workerId)
    {
        if (!static::$instance[$port][$workerId]) {
            if (Config::get('app.is_server')) {
                $uri = Config::get('mongo.server.url', '');
            } else {
                $uri = Config::get('mongo.local.url', '');
            }
            if ($uri) {
                self::setInstance($port, $workerId, new MongoDbClient($uri));
            }
        }
    }

    /**
     * Set the application instance.
     *
     * @param int $port
     * @param int $workerId
     * @param  MongoDbClient $instance
     * @return void
     */
    public static function setInstance(int $port, int $workerId, MongoDbClient $instance)
    {
        static::$instance[$port][$workerId] = $instance;
    }

    /**
     * 返回当前实体类实例
     * @return MongoDbClient
     */
    public static function getInstance()
    {
        return self::$instance;
    }

    /**
     * 删除mysql单例
     */
    public static function delInstance()
    {
        static::$instance = null;
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
            throw new \Exception('找不到Mongo数据库对象');
        }

        return $instance->$method(...$args);
    }
}