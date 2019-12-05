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
use Illuminate\Database\Query\Expression;
use Library\Entity\Model\DataBase\EntityMysql;
use Library\Pool\CoroutineMysqlClientPool;
use Swoole\Coroutine\MySQL;

/**
 * Class BuilderObject
 * @method BuilderObject select(array | mixed $columns = ['*'])
 * @method BuilderObject selectRaw(string $expression, array $bindings = [])
 * @method BuilderObject where(string | array | Closure $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 * @method BuilderObject whereIn(string $column, mixed $values, string $boolean = 'and', bool $not = false)
 * @method string getDefaultConnection()
 * @method void setDefaultConnection(string $name)
 * @method BuilderObject table(string $table)
 * @method BuilderObject groupBy(array ...$groups)
 * @method BuilderObject orderBy(string $column, string $direction)
 * @method mixed selectOne(string $query, array $bindings = [])
 * @method bool insert(array $values)
 * @method int update(array $values, array $bindings = [])
 * @method int delete(string $query, array $bindings = [])
 * @method int count()
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
 * @method BuilderObject forPage(int $page, int $perSize)
 * @method BuilderObject skip(int $value)
 * @method BuilderObject limit(int $value)
 * @method array first(array | mixed $columns = ['*'])
 * @method string toSql()
 * @method array getBindings()
 * @method Expression raw(mixed $value)
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

        $this->client = CoroutineMysqlClientPool::get();
    }

    /**
     * @param $name
     * @param $arguments
     * @return array|bool|int|BuilderObject
     */
    public function __call($name, $arguments)
    {
        switch ($name) {
            case 'get':
                return $this->builderDo();
            case 'first':
                $this->builder->take(1);
                $result = $this->builderDo();
                return $result ? $result[0] : false;
            case 'count':
                $this->builder->selectRaw('count(*) as number');
                $result = $this->builderDo();
                return $result ? (int)$result[0]['number'] : 0;
            case 'beginTransaction':
                return $this->client->begin();
            case 'commit':
                return $this->client->commit();
            case 'rollBack':
                return $this->client->rollback();
            case 'toSql':
                return $this->builder->toSql();
            case 'getBindings':
                return $this->builder->getBindings();
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
        CoroutineMysqlClientPool::back($this->client);
    }
}