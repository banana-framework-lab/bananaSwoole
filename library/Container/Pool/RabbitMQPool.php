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
use Library\Exception\LogicException;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Swoole\Coroutine\Channel;

class RabbitMQPool
{
    /**
     * RabbitMQ连接池
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
    public function __construct(string $configName)
    {
        $poolSize = Container::getConfig()->get('pool.rabbitmq.size', 5);
        if (!$poolSize) {
            throw new LogicException('请配置具体rabbitmq连接池大小');
        }
        $this->pool = new Channel(
            $poolSize
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
    private function getClient(string $configName): AMQPStreamConnection
    {
        if (!$configName) {
            $configName = Container::getServerConfigIndex();
        }

        $rabbitConfig = Container::getConfig()->get("rabbit.{$configName}");

        if ($rabbitConfig) {
            return new AMQPStreamConnection(
                $rabbitConfig['host'],
                $rabbitConfig['port'],
                $rabbitConfig['user'],
                $rabbitConfig['password'],
                $rabbitConfig['vhost']
            );
        } else {
            throw new Exception('请配置RabbitMQ信息');
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
    public function back(AMQPStreamConnection $client)
    {
        $this->pool->push($client);
    }
}

