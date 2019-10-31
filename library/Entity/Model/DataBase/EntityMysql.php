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
use Throwable;

/**
 * Class EntityMysql
 * @package Library\Entity\Model\DataBase
 *
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

    /**
     * EntityMysql constructor.
     */
    private function __construct()
    {
    }

    /**
     * EntityMysql clone.
     */
    private function __clone()
    {
    }

    /**
     * 初始化Mysql实体对象
     * @param int $workerId
     * @throws Throwable
     */
    public static function instanceStart(int $workerId)
    {
        if (!static::$instance[$workerId]) {
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
                    //真正连接数据库
                    $mysqlClient->connection()->getPdo();

                    //设置mysql全局对象
                    self::setInstance($workerId, $mysqlClient);
                }
            } catch (Throwable $exception) {
                throw $exception;
            }
        }
    }

    /**
     * 保存Mysql实体对象
     * @param int $workerId
     * @param MysqlClient $instance
     * @return void
     */
    private static function setInstance(int $workerId, MysqlClient $instance)
    {
        static::$instance[$workerId] = $instance;
    }

    /**
     * @return MysqlClient
     */
    public static function getInstance(int $workerId)
    {
        return self::$instance[$workerId];
    }

    /**
     * @param $method
     * @param $args
     * @return mixed
     * @throws \Exception
     */
    public static function __callStatic($method, $args)
    {
        $workerId = EntitySwooleWebSever::getInstance()->worker_id;
        $instance = self::$instance[$workerId];

        if (!$instance) {
            throw new \Exception('找不到Mysql数据库对象');
        } else {
            if (method_exists($instance, $method)) {
                return $instance->$method(...$args);
            } else {
                throw new \Exception("Mysql数据库对象没有方法{$method}");
            }
        }

    }
}