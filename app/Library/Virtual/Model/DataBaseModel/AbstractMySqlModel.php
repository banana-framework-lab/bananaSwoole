<?php
/**
 * MySQL的Model抽象类
 * User: zzh
 * Date: 2018/10/10
 * Time: 17:28
 */

namespace App\Library\Virtual\Model\DataBaseModel;

use App\Library\Entity\Model\DataBase\Mysql;
use App\Library\Virtual\Property\AbstractProperty;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Connection;

/**
 * @property Builder builder
 * @property String tableName
 * @property Connection connection
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

    /**
     * @var string getList中需要查询的列名
     */
    private $listColumns = '*';

    /**
     * @param array $where 查询条件
     * @param array $orderBy 排序条件
     * @return \Illuminate\Database\Query\Builder 查询构造器对象
     */
    abstract protected function getCondition($where, $orderBy = []);

    /**
     * 设置getList中需要查询的列名
     * @param string $columns
     */
    public function setListColumns($columns)
    {
        $this->listColumns = $columns;
    }

    /**
     * 根据条件筛选列表
     * @param array $where
     * @param array $orderBy
     * @return \Illuminate\Support\Collection
     */
    public function getList($where = [], $orderBy = [])
    {
        $builder = $this->getCondition($where, $orderBy);
        if ($this->listColumns != '*') {
            $builder->select(explode(',', $this->listColumns));
        }
        return $builder->get();
    }

    /**
     * 根据条件筛选一个
     * @param array $where
     * @param string $columns
     * @return Model|Builder|null|object
     */
    public function getFirst($where, $columns = '*')
    {
        unset($where['page'], $where['limit']);
        $builder = $this->getCondition($where);
        if ($this->listColumns != '*') {
            $builder->select(explode(',', $this->listColumns));
        }
        return $builder->first($columns);
    }

    /**
     * 根据条件筛选数量
     * @param $where
     * @return int
     */
    public function getCount($where)
    {
        unset($where['page'], $where['limit']);
        return $this->getCondition($where)->count();
    }

    /**
     * 新增一个数据
     * @param AbstractProperty $addObject
     * @return bool
     */
    public function addOne($addObject)
    {
        foreach ($addObject->toArray() as $key => $value) {
            if ($key != 'id') {
                $this->$key = $value;
            }
        }
        return $this->save();
    }

    /**
     * 更新一个数据
     * @param AbstractProperty $updateInfo
     * @return int
     */
    public function updateOne($updateInfo)
    {
        foreach ($updateInfo->toArray() as $key => $value) {
            $this->$key = $value;
        }
        return $this->save();
    }


    /**
     * 批量更新
     * @param array $update
     * @param string $whenField
     * @param string $whereField
     * @return int
     */
    function updateBatch($update, $whenField = 'id', $whereField = 'id')
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

    /**
     * 获取数据库对象
     * @param $name
     * @return Connection|Builder|string
     */
    public function __get($name)
    {
        if ($name === 'builder') {
            return Mysql::table($this->table);
        } else if ($name === 'connection') {
            return Mysql::connection();
        } else if ($name === 'tableName') {
            return $this->table;
        }
        return null;
    }
}