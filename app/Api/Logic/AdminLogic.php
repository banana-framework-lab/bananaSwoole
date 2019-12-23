<?php

namespace App\Api\Logic;

use App\Api\Model\DataBaseModel\AdminCoroutineModel;
use Library\Response;

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
     * @throws \Exception
     */
    public function login(string $username, string $password): array
    {
        $adminModel = new AdminCoroutineModel();
        $userInfo = $adminModel->login($username, $password);
        if ($userInfo) {
            $userInfo->permission = $this->getPermission((int)$userInfo->id);
            return $userInfo->toArray();
        } else {
            return [];
        }
    }

    /**
     * @param int $uid
     * @return array
     */
    public function getPermission(int $uid): array
    {
        return [
            [
                'path' => '/form',
                'children' => [
                    [
                        'path' => 'index'
                    ]
                ]
            ]
        ];
    }
}