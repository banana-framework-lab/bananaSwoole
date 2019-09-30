<?php
/**
 * 全局DB类
 * User: zzh
 * Date: 2018/10/10
 */

namespace App\Library\Entity\Model\DataBase;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Expression;

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
class Mysql
{
    public static $instance;

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    /**
     * Set the application instance.
     *
     * @param  Capsule $instance
     * @return void
     */
    public static function setDBInstance($instance)
    {
        if (!static::$instance) {
            static::$instance = $instance;
        }
    }

    /**
     * 删除mysql单例
     */
    public static function recoverDBInstance()
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
        $instance = self::$instance;

        if (!$instance) {
            throw new \Exception('找不到数据库对象');
        }

        return $instance->$method(...$args);
    }
}