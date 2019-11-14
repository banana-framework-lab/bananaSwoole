<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/10/30
 * Time: 15:23
 */

namespace Library\Entity\MessageQueue;

use Library\Config;
use PhpAmqpLib\Connection\AMQPSwooleConnection;

class EntitySwooleRabbit
{
    /**
     * @var AMQPSwooleConnection $instance ;
     */
    private static $instance;

    /**
     * 初始化实体对象
     */
    public static function instanceStart()
    {
        if (!static::$instance) {
            $rabbitConfig = Config::get('app.is_server') ? Config::get('rabbit.server') : Config::get('rabbit.local');

            $rabbitClient = new AMQPSwooleConnection(
                $rabbitConfig['host'],
                $rabbitConfig['port'],
                $rabbitConfig['user'],
                $rabbitConfig['password'],
                $rabbitConfig['vhost']
            );

            self::setInstance($rabbitClient);
        }
    }

    /**
     * 删除rabbit实体单例
     */
    public static function delInstance()
    {
        static::$instance = null;
    }

    /**
     * @param AMQPSwooleConnection $instance
     */
    public static function setInstance(AMQPSwooleConnection $instance)
    {
        static::$instance = $instance;
    }

    /**
     * 返回当前实体类实例
     * @return AMQPSwooleConnection
     */
    public static function getInstance(): AMQPSwooleConnection
    {
        return self::$instance;
    }
}