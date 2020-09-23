<?php
/**
 * MySQL的Model抽象类
 * User: zzh
 * Date: 2018/10/10
 * Time: 17:28
 */

namespace Library\Virtual\Model\DatabaseModel;

use Illuminate\Support\Collection;
use Library\Entity\Model\Database\EntityMysql;
use Library\Entity\Swoole\EntitySwooleServer;
use Library\Object\BuilderObject;
use Library\Virtual\Property\AbstractProperty;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Connection;

/**
 * Class AbstractMySqlModel
 * @property Builder builder
 * @property String tableName
 * @property Connection connection
 * @package Library\Virtual\Model\DataBaseModel
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
     * 获取数据库对象
     * @param $name
     * @return Connection|Builder|BuilderObject|string
     */
    public function __get($name)
    {
        switch ($name) {
            case 'connection':
                if (EntitySwooleServer::getInstance()) {
                    return new BuilderObject($this->table);
                } else {
                    return EntityMysql::table($this->table);
                }
            case 'builder':
                return EntityMysql::table($this->table);
            case 'tableName':
                return $this->table;
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