<?php
/**
 * Created by PhpStorm.
 * User: ZhongHao-Zh
 * Date: 2019/10/26
 * Time: 20:18
 */

namespace App\Demo\Model\DatabaseModel;

use Library\Virtual\Model\DatabaseModel\AbstractMySqlModel;


class DemoMysqlModel extends AbstractMySqlModel
{
    /**
     * AdminModel constructor.
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        // todo 这里需要给table指定table名称
        $this->table = 'test';
        parent::__construct($attributes);
    }

    public function getList()
    {
        $builder = $this->builder;
        $builder->selectRaw('SLEEP(10)');
        return $builder->get();
    }
}