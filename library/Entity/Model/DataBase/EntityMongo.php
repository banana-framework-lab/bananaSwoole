<?php
/**
 * MongoDB实体类
 * User: zzh
 * Date: 2018/10/10
 */

namespace Library\Entity\Model\DataBase;

use Library\Config;
use MongoDB\Client as MongoDbClient;

/**
 * Class EntityMongo
 * @package Library\Entity\Model\DataBase
 */
class EntityMongo
{
    /**
     * @var MongoDbClient $instance
     */
    private static $instance;

    /**
     * EntityMongo constructor.
     */
    private function __construct()
    {
    }

    /**
     *  EntityMongo clone
     */
    private function __clone()
    {
    }

    /**
     * 初始化Mongo实体对象
     */
    public static function instanceStart()
    {
        if (!static::$instance) {
            if (Config::get('app.is_server')) {
                $uri = Config::get('mongo.server.url', '');
            } else {
                $uri = Config::get('mongo.local.url', '');
            }
            if ($uri) {
                $mongodbInstance = new MongoDbClient($uri);
                //访问数据库，确认连接成功
                $mongodbInstance->listDatabases();

                //设置mongo全局对象
                self::setInstance($mongodbInstance);
            }
        }
    }

    /**
     * 保存Mongo实体对象
     * @param  MongoDbClient $instance
     * @return void
     */
    private static function setInstance(MongoDbClient $instance)
    {
        static::$instance = $instance;
    }

    /**
     * 删除mongo数据库连接对象
     */
    public static function deleteInstance()
    {
        if (static::$instance) {
            static::$instance = null;
        }
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
     * 静态调用Mongo方法
     * @param $method
     * @param $args
     * @return mixed
     * @throws \Exception
     */
    public static function __callStatic($method, $args)
    {
        $instance = self::$instance;

        if (!$instance) {
            throw new \Exception('找不到Mongo数据库对象');
        } else {
            if (method_exists($instance, $method)) {
                return $instance->$method(...$args);
            } else {
                throw new \Exception("Mongo数据库对象没有方法{$method}");
            }
        }
    }
}