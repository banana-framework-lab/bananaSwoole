<?php

namespace App\Api\Logic;

use App\Api\Model\DataBaseModel\AdminCoroutineModel;

/**
 * Created by PhpStorm.
 * User: ZhongHao-Zh
 * Date: 2019/10/26
 * Time: 20:15
 */
class AdminLogic
{
    /**
     * @param string $username
     * @param string $password
     * @return array
     */
    public function login(string $username, string $password): array
    {
        $adminModel = new AdminCoroutineModel();
        $userInfo = $adminModel->login($username, $password);
        $token = "@#$#@$#@$@#@#@#$@#";
        return [
        ];
    }
}