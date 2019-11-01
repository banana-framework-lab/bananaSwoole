<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/10/30
 * Time: 15:23
 */

namespace Library\Entity\MessageQueue;

use Library\Config;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class EntityRabbit
{
    /**
     * @var AMQPStreamConnection $instance ;
     */
    private static $instance;

    /**
     * 初始化实体对象
     */
    public static function instanceStart()
    {
        if (!static::$instance) {
            $rabbitConfig = Config::get('app.is_server') ? Config::get('rabbit.server') : Config::get('rabbit.local');

            $rabbitClient = new AMQPStreamConnection(
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
     * @param AMQPStreamConnection $instance
     */
    public static function setInstance(AMQPStreamConnection $instance)
    {
        static::$instance = $instance;
    }

    /**
     * 返回当前实体类实例
     * @return AMQPStreamConnection
     */
    public static function getInstance()
    {
        return self::$instance;
    }
}