<?php

namespace App\Api\Logic;

use App\Api\Model\DataBaseModel\AdminCoroutineModel;
use App\Api\Model\DataBaseModel\AdminModel;
use App\Api\Model\DataBaseModel\NumberModel;
use Co;
use Library\Helper\LogHelper;

/**
 * Created by PhpStorm.
 * User: ZhongHao-Zh
 * Date: 2019/10/26
 * Time: 20:15
 */
class TestLogic
{
    public function sqlCover()
    {
        $adminModel = new AdminModel();
        $builder = $adminModel->builder;
        $builder->where('username', '=', time());
        Co::sleep(3.0);
        $sql = $builder->toSql();
        $bindings = $builder->getBindings();
        $builder->get();
        LogHelper::info('cover sql', ['sql' => $sql, 'bindings' => $bindings]);
    }

    /**
     * 登陆判断
     * @param string $username
     * @param string $password
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Query\Builder|null|object
     */
    public function login(string $username, string $password)
    {
        $adminModel = new AdminModel();
        $result = $adminModel->login($username, $password);
        return $result;
    }

    /**
     * 协程登陆判断
     * @param string $username
     * @param string $password
     * @return array|bool
     */
    public function coroutineLogin(string $username, string $password)
    {
        $adminModel = new AdminCoroutineModel();
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