<?php
/**
 * 协程MySQL的Model抽象类
 * User: zzh
 * Date: 2018/10/10
 * Time: 17:28
 */

namespace Library\Virtual\Model\DataBaseModel;

use Library\Object\BuilderObject;
use Library\Virtual\Property\AbstractProperty;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Connection;

/**
 * Class AbstractCoroutineMySqlModel
 * @property BuilderObject builder
 * @property String tableName
 * @property Connection connection
 * @package Library\Virtual\Model\DataBaseModel
 */
abstract class AbstractCoroutineMySqlModel extends Model
{
    /**
     * @var array getList中需要查询的列名
     */
    private $listColumns = ['*'];

    /**
     * 获取数据库对象
     * @param $name
     * @return Connection|BuilderObject|string
     */
    public function __get($name)
    {
        switch ($name) {
            case 'builder':
                if ($this->table) {
                    return new BuilderObject($this->table);
                } else {
                    return null;
                }
            case 'tableName':
                return $this->table;
            default:
                return null;
        }
    }

    /**
     * @param array $where 查询条件
     * @param array $orderBy 排序条件
     * @return BuilderObject 查询构造器对象
     */
    abstract protected function getCondition($where, $orderBy = []): BuilderObject;

    /**
     * 设置getList中需要查询的列名
     * @param string|array $columns
     */
    public function setListColumns($columns)
    {
        if (is_array($columns)) {
            $this->listColumns = $columns;
        } elseif (is_string($columns)) {
            $this->listColumns = explode(',', $columns);
        }
    }

    /**
     * 根据条件筛选列表
     * @param array $where
     * @param array $orderBy
     * @return array
     */
    public function getList($where = [], $orderBy = [])
    {
        $builder = $this->getCondition($where, $orderBy);
        if ($this->listColumns != ['*']) {
            $builder->select($this->listColumns);
            $this->listColumns = ['*'];
        }
        $result = $builder->get();
        return $result;
    }

    /**
     * 根据条件筛选一个
     * @param array $where
     * @param array $columns
     * @return array
     */
    public function getFirst($where, $columns = ['*'])
    {
        unset($where['page'], $where['limit']);
        $builder = $this->getCondition($where);
        if ($columns == ['*'] && $this->listColumns != ['*']) {
            $columns = $this->listColumns;
            $this->listColumns = ['*'];
        }
        return $builder->select($columns)->first();
    }

    /**
     * 根据条件筛选数量
     * @param $where
     * @return int
     */
    public function getCount($where): int
    {
        unset($where['page'], $where['limit']);
        return $this->getCondition($where)->count();
    }

    /**
     * 新增一个数据
     * @param AbstractProperty $addObject
     * @return bool
     */
    public function addOne($addObject): bool
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
     * @param array $updateInfo
     * @return int
     */
    public function updateOne($updateInfo): int
    {
        foreach ($updateInfo as $key => $value) {
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
    function updateBatch($update, $whenField = 'id', $whereField = 'id'): int
    {
        $when = [];
        $ids = [];
        foreach ($update as $sets) {
            // 跳过没有更新主键的数据
            if (!isset($sets[$whenField])) continue;
            $whenValue = $sets[$whenField];

            foreach ($sets as $fieldName => $value) {
                // 主键不需要被更新
                if ($fieldName == $whenField) {
                    array_push($ids, $value);
                    continue;
                };

                $when[$fieldName][] = "when '{$whenValue}' then '{$value}'";
            }
        }

        // 没有更新的条件id
        if (!$when) return false;

        $builder = $this->builder->whereIn($whereField, $ids);

        // 组织sql
        foreach ($when as $fieldName => &$item) {
            $item = $this->builder->raw("case $whenField " . implode(' ', $item) . ' end ');
        }

        return $builder->update($when);
    }
}