<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/9/11 0011
 * Time: 9:48
 */

namespace App\Api\Model\DataBaseModel;

use App\Library\Virtual\Model\DataBaseModel\AbstractMySqlModel;

class SessionList extends AbstractMySqlModel
{
    protected $table = "session_list";

    protected $fillable = ['platform_id', 'personal_uid', 'opponent_uid', 'window_id'];

    /**
     * @param array $where 查询条件
     * @param array $orderBy 排序条件
     * @return \Illuminate\Database\Query\Builder 查询构造器对象
     */
    protected function getCondition($where, $orderBy = [])
    {
        $builder = $this->builder;
        //组装where条件
        foreach ($where as $wKey => $wValue) {
            switch ($wKey) {
                case 'platform_id':
                    $builder->where('platform_id', '=', $where['platform_id']);
                    break;
                case 'personal_uid':
                    $builder->where('personal_uid', '=', $where['personal_uid']);
                    break;
                case 'opponent_uid':
                    $builder->where('opponent_uid', '=', $where['opponent_uid']);
                    break;
            }
        }
        //组装orderBy条件
        foreach ($orderBy as $oKey => $oValue) {
            switch ($oKey) {
                case 'create_time':
                    $builder->orderBy('create_time', $orderBy['create_time']);
                    break;
            }
        }
        //组装分页条件
        if ($where['page'] && $where['limit']) {
            $builder->forPage($where['page'], $where['limit']);
        }
        return $builder;
    }
}