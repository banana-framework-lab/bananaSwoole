<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/10/30
 * Time: 15:23
 */

namespace Library\Entity\MessageQueue;

use Library\Config;
use Library\Entity\Swoole\EntitySwooleWebSever;
use Library\Entity\Swoole\EntitySwooleWebSocketSever;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class EntityRabbit
{
    /**
     * @var array $instance ;
     */
    private static $instance;

    /**
     * @param int $workerId
     */
    public static function instanceStart(int $workerId)
    {
        if (!static::$instance[$workerId]) {
            $rabbitConfig = Config::get('app.is_server') ? Config::get('rabbit.server') : Config::get('rabbit.local');

            $rabbitClient = new AMQPStreamConnection(
                $rabbitConfig['host'],
                $rabbitConfig['port'],
                $rabbitConfig['user'],
                $rabbitConfig['password'],
                $rabbitConfig['vhost']
            );

            self::setInstance($workerId, $rabbitClient);
        }
    }

    /**
     * @param int $workerId
     * @param AMQPStreamConnection $instance
     */
    public static function setInstance(int $workerId, AMQPStreamConnection $instance)
    {
        static::$instance[$workerId] = $instance;
    }

    /**
     * 返回当前实体类实例
     * @return AMQPStreamConnection
     */
    public static function getInstance()
    {
        $workerId = EntitySwooleWebSocketSever::getInstance()->worker_id;
        return self::$instance[$workerId];
    }
}