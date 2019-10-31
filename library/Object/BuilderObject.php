<?php
/**
 * Created by PhpStorm.
 * User: ZhongHao-Zh
 * Date: 2019/10/30
 * Time: 22:37
 */

namespace Library\Object;

use Closure;
use Illuminate\Database\Query\Builder;
use Library\Config;
use Library\Entity\Model\DataBase\EntityMysql;
use Library\Helper\RequestHelper;
use Library\Helper\ResponseHelper;
use Swoole\Coroutine\MySQL;

/**
 * Class BuilderObject
 * @method BuilderObject select(array | mixed $columns = ['*'])
 * @method BuilderObject selectRaw(string $expression, array $bindings = [])
 * @method BuilderObject where(string | array | Closure $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 * @method string getDefaultConnection()
 * @method void setDefaultConnection(string $name)
 * @method BuilderObject table(string $table)
 * @method mixed selectOne(string $query, array $bindings = [])
 * @method bool insert(string $query, array $bindings = [])
 * @method int update(string $query, array $bindings = [])
 * @method int delete(string $query, array $bindings = [])
 * @method bool statement(string $query, array $bindings = [])
 * @method int affectingStatement(string $query, array $bindings = [])
 * @method bool unprepared(string $query)
 * @method array prepareBindings(array $bindings)
 * @method mixed transaction(\Closure $callback, int $attempts = 1)
 * @method void beginTransaction()
 * @method void commit()
 * @method void rollBack()
 * @method int transactionLevel()
 * @method array pretend(\Closure $callback)
 * @method array get(array | mixed $columns = ['*'])
 * @method array first(array | mixed $columns = ['*'])
 * @package Library\Object
 */
class BuilderObject
{
    /**
     * @var string $table
     */
    private $table;

    /**
     * @var Builder $builder
     */
    private $builder;

    /**
     * @var MySQL $client
     */
    private $client;

    /**
     * BuilderObject constructor.
     * @param string $table
     */
    public function __construct(string $table)
    {
        $this->table = $table;

        $this->builder = EntityMysql::table($this->table);

        $mysqlConfig = Config::get('mysql.local');
        $coroutineMysqlConfig = [
            'host' => $mysqlConfig['host'],
            'port' => $mysqlConfig['port'] ?? 3306,
            'user' => $mysqlConfig['username'],
            'password' => $mysqlConfig['password'],
            'database' => $mysqlConfig['database'],
        ];
        $this->client = new MySQL('mysql');
        $this->client->connect($coroutineMysqlConfig);

//        var_dump($coroutineMysqlConfig);
//        var_dump($this->client);
//        ResponseHelper::exit();
    }

    /**
     * @param $name
     * @param $arguments
     * @return array|bool|int|BuilderObject
     */
    public function __call($name, $arguments)
    {
        switch ($name) {
            case 'first':
                $this->builder->take(1);
                $result = $this->builderDo();
                return $result ? $result[0] : false;
            case 'get':
                return $this->builderDo();
            case  'beginTransaction':
                return $this->client->begin();
            case  'commit':
                return $this->client->commit();
            case  'rollBack':
                return $this->client->rollback();
            default:
                $this->builder->$name(...$arguments);
                return $this;
        }
    }

    /**
     * @return array|bool
     */
    private function builderDo()
    {
        $sqlObject = $this->client->prepare($this->builder->toSql());
        if ($sqlObject == false) {
            return false;
        } else {
            return $sqlObject->execute($this->builder->getBindings());
        }
    }

    /**
     * BuilderObject destruct.
     */
    public function __destruct()
    {
        // TODO: 把client成员变量怼回去连接池
    }
}