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
use MongoDB\Client;
use Swoole\Coroutine\Channel;

class MongoPool
{
    /**
     * Mongo连接池
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
            Container::getConfig()->get('pool.mongo.size', 5)
        );
        for ($i = 1; $i <= $this->poolSize; $i++) {
            $this->pool->push($this->getClient($configName));
        }
    }

    /**
     * 获取mongo客户端连接
     * @param string $configName
     * @return Client
     * @throws Exception
     */
    private function getClient($configName = 'server')
    {
        $mongoUri = Container::getConfig()->get("mongo.{$configName}.url", '');

        if ($mongoUri) {
            $mongodbServer = new Client($mongoUri);
            //访问数据库，确认连接成功
            $mongodbServer->listDatabases();

            return $mongodbServer;
        } else {
            throw new Exception('请配置mongo信息');
        }
    }

    /**
     * 获取连接
     * @return Client
     */
    public function get(): Client
    {
        return $this->pool->pop();
    }

    /**
     * 归还连接
     * @param Client $client
     */
    public function back(Client $client)
    {
        $this->pool->push($client);
    }
}

