<?php
/**
 * MysqlDB实体类
 * User: zzh
 * Date: 2018/10/10
 */

namespace Library\Entity\Model\DataBase;

use Exception;
use Illuminate\Database\Capsule\Manager as MysqlClient;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Expression;
use Library\Config;
use Library\Entity\Swoole\EntitySwooleWebSever;

/**
 * @method static Connection connection(string $name = null)
 * @method static string getDefaultConnection()
 * @method static void setDefaultConnection(string $name)
 * @method static Builder table(string $table)
 * @method static Expression raw($value)
 * @method static mixed selectOne(string $query, array $bindings = [])
 * @method static array select(string $query, array $bindings = [])
 * @method static bool insert(string $query, array $bindings = [])
 * @method static int update(string $query, array $bindings = [])
 * @method static int delete(string $query, array $bindings = [])
 * @method static bool statement(string $query, array $bindings = [])
 * @method static int affectingStatement(string $query, array $bindings = [])
 * @method static bool unprepared(string $query)
 * @method static array prepareBindings(array $bindings)
 * @method static mixed transaction(\Closure $callback, int $attempts = 1)
 * @method static void beginTransaction()
 * @method static void commit()
 * @method static void rollBack()
 * @method static int transactionLevel()
 * @method static array pretend(\Closure $callback)
 *
 * @see \Illuminate\Database\DatabaseManager
 * @see \Illuminate\Database\Connection
 */
class EntityMysql
{
    /**
     * @var MysqlClient
     */
    private static $instance;

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    /**
     * 初始化Mysql实体对象
     * @param int $port
     * @param int $workerId
     * @throws Exception
     */
    public static function instanceStart(int $port, int $workerId)
    {
        if (!static::$instance[$port][$workerId]) {
            try {
                if (Config::get('app.is_server')) {
                    $configData = Config::get('mysql.server', []);
                } else {
                    $configData = Config::get('mysql.local', []);
                }
                if ($configData) {
                    $mysqlClient = new MysqlClient;
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
                    //初始化mysql全局对象
                    self::setInstance($port, $workerId, $mysqlClient);
                }
            } catch (Exception $exception) {
                throw $exception;
            }
        }
    }

    /**
     * Set the application instance.
     *
     * @param int $port
     * @param int $workerId
     * @param MysqlClient $instance
     * @return void
     */
    public static function setInstance(int $port, int $workerId, MysqlClient $instance)
    {
        static::$instance[$port][$workerId] = $instance;
    }

    /**
     * @return MysqlClient
     */
    public static function getInstance()
    {
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
        $workId = EntitySwooleWebSever::getInstance()->worker_id;
        $instance = self::$instance[$workId];

        if (!$instance) {
            throw new \Exception('找不到Mysql数据库对象');
        }

        return $instance->$method(...$args);
    }
}