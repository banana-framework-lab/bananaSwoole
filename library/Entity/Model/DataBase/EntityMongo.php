<?php
/**
 * MongoDB实体类
 * User: zzh
 * Date: 2018/10/10
 */

namespace Library\Entity\Model\DataBase;

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
     */
    public static function instanceStart()
    {
        if (!static::$instance) {
            $uri = IS_SERVER ? MONGO_LIST['server'] : MONGO_LIST['local'];
            self::setInstance(new MongoDbClient($uri));
        }
    }

    /**
     * Set the application instance.
     *
     * @param  MongoDbClient $instance
     * @return void
     */
    public static function setInstance(MongoDbClient $instance)
    {
        if (!static::$instance) {
            static::$instance = $instance;
        }
    }

    /**
     * 返回当前实体类实例
     * @return MongoDbClient
     */
    public static function getInstance(){
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
        $instance = self::$instance;

        if (!$instance) {
            throw new \Exception('找不到数据库对象');
        }

        return $instance->$method(...$args);
    }
}