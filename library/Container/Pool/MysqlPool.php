<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/10/31
 * Time: 16:41
 */

namespace Library\Container\Pool;

use Exception;
use Illuminate\Database\Capsule\Manager;
use Library\Container;
use Library\Exception\LogicException;
use Swoole\Coroutine\Channel;

class MysqlPool
{
    /**
     * 数据库连接池
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
        $poolSize = Container::getConfig()->get('pool.mysql.size');
        if (!$poolSize) {
            throw new LogicException('请配置具体mysql连接池大小');
        }
        $this->pool = new Channel($poolSize);
        for ($i = 1; $i <= $this->poolSize; $i++) {
            $this->pool->push($this->getClient($configName));
        }
    }

    /**
     * 获取
     * @param string $configName
     * @return Manager
     */
    private function getClient(string $configName): Manager
    {
        if (!$configName) {
            $configName = Container::getServerConfigIndex();
        }

        $configData = Container::getConfig()->get("mysql.$configName", []);

        if ($configData) {
            $mysqlClient = new Manager();
            //设置数据库的配置
            $mysqlClient->addConnection($configData);
            // 使得数据库对象全局可用
            $mysqlClient->setAsGlobal();
            //设置可用Eloquent
            $mysqlClient->bootEloquent();
            //真正连接数据库
            $mysqlClient->connection()->getPdo();
            return $mysqlClient;
        } else {
            throw new LogicException('请配置MySQL信息');
        }
    }

    /**
     * 获取连接
     * @return Manager
     */
    public function get(): Manager
    {
        return $this->pool->pop();
    }

    /**
     * 归还连接
     * @param Manager $client
     */
    public function back(Manager $client)
    {
        $this->pool->push($client);
    }
}

