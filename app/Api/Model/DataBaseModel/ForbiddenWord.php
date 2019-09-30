<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/9/19 0019
 * Time: 14:23
 */

namespace App\Api\Model\DataBaseModel;

use App\Library\Virtual\Model\DataBaseModel\AbstractMySqlModel;

class ForbiddenWord extends AbstractMySqlModel
{
    protected $table = "forbidden_word";

    protected $fillable = ['content'];

    protected function getCondition($where, $orderBy = [])
    {
        return $this->builder;
    }
}