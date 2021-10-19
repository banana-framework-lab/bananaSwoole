<?php
/**
 * MySQL的Model抽象类
 * User: zzh
 * Date: 2018/10/10
 * Time: 17:28
 */

namespace Library\Abstracts\Model;

use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Query\Builder;
use Library\Container;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AbstractMySqlModel
 * @property Builder builder
 * @property String tableName
 * @package Library\Abstracts\Model\DataBaseModel
 */
abstract class AbstractMySqlModel extends Model
{
    /**
     * 返回查询构造器生成的SQL语句
     * @param Builder $builder
     * @return string|string[]|null
     */
    public function getSql($builder)
    {
        $bindings = $builder->getBindings();
        return preg_replace_callback('/\?/', function ($match) use (&$bindings) {
            $binding = array_shift($bindings);
            if (is_numeric($binding)) {
                return $binding;
            } else if (is_string($binding)) {
                return empty($binding) ? "''" : "'{$binding}'";
            } else {
                return $binding;
            }
        }, $builder->toSql());
    }

    /**
     * 自定义存储时间戳的字段名
     */
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';

    /**
     * @var string $dateFormat 模型中日期字段的存储格式
     */
    protected $dateFormat = 'U';

    /**
     * @var Manager $connection
     */
    protected $connection;

    /**
     * 构造函数-初始化connection
     * AbstractMySqlModel constructor.
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->connection = Container::getMysqlPool()->get();
        parent::__construct($attributes);
    }

    /**
     * 析构函数
     */
    public function __destruct()
    {
        Container::getMysqlPool()->back($this->connection);
    }

    /**
     * 获取数据库对象
     * @param $name
     * @return Builder|null
     */
    public function __get($name)
    {
        switch ($name) {
            case 'builder':
                return new Builder($this->connection->getConnection());
            default:
                return null;
        }
    }

    /**
     * 批量更新
     * @param array $update
     * @param string $whenField
     * @param string $whereField
     * @return int
     */
    function updateBatch($update, $whenField = 'id', $whereField = 'id'): int
    {
        $when = [];
        $ids = [];
        foreach ($update as $sets) {
            #　跳过没有更新主键的数据
            if (!isset($sets[$whenField])) continue;
            $whenValue = $sets[$whenField];

            foreach ($sets as $fieldName => $value) {
                #主键不需要被更新
                if ($fieldName == $whenField) {
                    array_push($ids, $value);
                    continue;
                };

                $when[$fieldName][] = "when '{$whenValue}' then '{$value}'";
            }
        }

        #　没有更新的条件id
        if (!$when) return false;

        $builder = $this->builder->whereIn($whereField, $ids);

        #　组织sql
        foreach ($when as $fieldName => &$item) {
            $item = $this->builder->raw("case $whenField " . implode(' ', $item) . ' end ');
        }

        return $builder->update($when);
    }
}