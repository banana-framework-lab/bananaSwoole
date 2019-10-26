<?php

namespace App\Api\Logic;

use App\Api\Model\DataBaseModel\AdminModel;
use App\Api\Model\DataBaseModel\NumberModel;

/**
 * Created by PhpStorm.
 * User: ZhongHao-Zh
 * Date: 2019/10/26
 * Time: 20:15
 */
class TestLogic
{
    /**
     * 登陆判断
     * @param string $username
     * @param string $password
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Query\Builder|null|object
     */
    public function login(string $username, string $password)
    {
        $adminModel = new AdminModel();
        return $adminModel->login($username, $password);
    }

    /**
     * @return array
     */
    public function getNumber()
    {
        $numberModel = new NumberModel();
        return $numberModel->getList();
    }
}