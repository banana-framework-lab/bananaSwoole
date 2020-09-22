<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/10/31
 * Time: 16:41
 */

namespace Library\Container\Pool;

use Exception;
use Library\Container;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Redis;
use Swoole\Coroutine\Channel;

class RabbitPool
{
    /**
     * Rabbit连接池
     * @var Channel $pool
     */
    private $pool;

    /**
     * 连接池大小
     * @var int $poolSize
     */
    private $poolSize = 5;

    /**
     * 初始化连接池
     * @param string $configName
     * @throws Exception
     */
    public function __construct($configName = 'server')
    {
        $this->pool = new Channel(
            Container::getConfig()->get('pool.redis.size', 5)
        );
        for ($i = 1; $i <= $this->poolSize; $i++) {
            $this->pool->push($this->getClient($configName));
        }
    }

    /**
     * 获取
     * @param string $configName
     * @return AMQPStreamConnection
     * @throws Exception
     */
    private function getClient($configName = 'server')
    {
        $rabbitConfig = Container::getConfig()->get("rabbit.{$configName}");

        if ($rabbitConfig) {
            $rabbitClient = new AMQPStreamConnection(
                $rabbitConfig['host'],
                $rabbitConfig['port'],
                $rabbitConfig['user'],
                $rabbitConfig['password'],
                $rabbitConfig['vhost']
            );

            return $rabbitClient;
        } else {
            throw new Exception('请配置rabbit信息');
        }
    }

    /**
     * 获取连接
     * @return AMQPStreamConnection
     */
    public function get(): AMQPStreamConnection
    {
        return $this->pool->pop();
    }

    /**
     * 归还连接
     * @param AMQPStreamConnection $client
     */
    public function back(Redis $client)
    {
        $this->pool->push($client);
    }
}

