<?php
/**
 * Created by PhpStorm.
 * User: ZhongHao-Zh
 * Date: 2019/10/26
 * Time: 20:18
 */

namespace App\Api\Model\DatabaseModel;

use Library\Object\BuilderObject;
use Library\Virtual\Model\DatabaseModel\AbstractCoroutineMySqlModel;


class DemoCoMysqlModel extends AbstractCoroutineMySqlModel
{
    /**
     * AdminModel constructor.
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        // todo 这里需要给table指定table名称
        $this->table = '';
        parent::__construct($attributes);
    }

    /**
     * @param array $where 查询条件
     * @param array $orderBy 排序条件
     * @return BuilderObject 查询构造器对象
     */
    protected function getCondition($where, $orderBy = []): BuilderObject
    {
        $builder = $this->builder;
        // todo 如果要用getList()这里设置查询条件
        if (isset($where['id'])) {
            $builder->where('id', '=', $where['username']);
        }
        return $builder;
    }

    public function demo()
    {
        // todo $result需要通过build来查询出接口返回
        $result = ['id' => 1, 'msg' => 'demo'];
        return $result;
    }
}