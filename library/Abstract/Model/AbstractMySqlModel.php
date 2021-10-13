<?php
/**
 * MySQL的Model抽象类
 * User: zzh
 * Date: 2018/10/10
 * Time: 17:28
 */

namespace Library\Virtual\Model\DatabaseModel;

use Library\Container;
use Library\Entity\EntityMysqlBuilder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AbstractMySqlModel
 * @property EntityMysqlBuilder builder
 * @property String tableName
 * @package Library\Abstract\Model\DataBaseModel
 */
abstract class AbstractMySqlModel extends Model
{
    /**
     * 自定义存储时间戳的字段名
     */
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';

    /**
     * @var string $dateFormat 模型中日期字段的存储格式
     */
    protected $dateFormat = 'U';

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
     * @return EntityMysqlBuilder|null
     */
    public function __get($name)
    {
        switch ($name) {
            case 'builder':
                return new EntityMysqlBuilder($this->connection);
            default:
                return null;
        }
    }

    /**
     * 返回查询构造器生成的SQL语句
     * @param EntityMysqlBuilder $builder
     * @return string|string[]|null
     */
    public function getSql(EntityMysqlBuilder $builder)
    {
        return $builder->getSql();
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