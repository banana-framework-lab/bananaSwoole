<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/9/5 0005
 * Time: 16:28
 */

namespace App\Api\Controller;


use App\Api\Logic\AdminLogic;
use App\Api\Model\CacheModel\SessionModel;

class AdminController extends BaseController
{
    /**
     * 管理员登录
     * @return array
     */
    public function login()
    {
        $result = (new AdminLogic())->login($this->request['username'], $this->request['password']);
        if ($result) {
            (new SessionModel())->setSessionInfo($this->sessionId, $result);
            return [
                'code' => 20000,
                'data' => $result
            ];
        }
        return [
            'code' => 60204,
            'message' => 'Account and password are incorrect.'
        ];
    }

    /**
     * 获取管理员账号数据
     * @return array
     */
    public function info()
    {
        return [
            'code' => 20000,
            'data' => $this->sessionInfo
        ];
    }
}