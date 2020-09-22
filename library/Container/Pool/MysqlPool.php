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
use Library\Config;
use PDO;
use Swoole\Coroutine\Channel;

class MysqlPool
{
    /**
     * 数据库连接池
     * @var Channel $pool
     */
    private static $pool;

    /**
     * 连接池大小
     * @var int $poolSize
     */
    private static $poolSize = 5;


    /**
     * 初始化连接池
     * @throws Exception
     */
    public static function poolInit()
    {
        self::$pool = new Channel(Config::get('pool.mysql.size', 5));
        for ($i = 1; $i <= self::$poolSize; $i++) {
            self::$pool->push(self::getClient());
        }
    }

    /**
     * 获取
     * @return Manager
     * @throws Exception
     */
    private static function getClient()
    {
        if (Config::get('app.is_server')) {
            $configData = Config::get('mysql.server', []);
        } else {
            $configData = Config::get('mysql.local', []);
        }
        if ($configData) {
            $mysqlClient = new Manager();
            //设置数据库的配置
            $mysqlClient->addConnection($configData);
            // 使得数据库对象全局可用
            $mysqlClient->setAsGlobal();
            //设置可用Eloquent
            $mysqlClient->bootEloquent();
            //非服务器下开启日志
            if (!Config::get('app.is_server')) {
                $mysqlClient->connection()->enableQueryLog();
            }
            //真正连接数据库
            $mysqlClient->connection()->getPdo();
            return $mysqlClient;
        } else {
            throw new Exception('请配置mysql信息');
        }
    }

    /**
     * 获取连接
     * @return Manager
     */
    public static function get(): Manager
    {
        var_dump(self::$pool->stats());
        return self::$pool->pop();
    }

    /**
     * 归还连接
     * @param Manager $client
     */
    public static function back(Manager $client)
    {
        var_dump(self::$pool->stats());
        self::$pool->push($client);
    }
}

