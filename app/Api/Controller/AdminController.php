<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/9/5 0005
 * Time: 16:28
 */

namespace App\Api\Controller;


use App\Api\Logic\AdminLogic;
use Library\Helper\ResponseHelper;

class AdminController extends BaseController
{
    public function login()
    {
        $result = (new AdminLogic())->login($this->request['username'], $this->request['password']);
        if ($result) {
            ResponseHelper::json([
                'code' => 20000,
                'data' => $result['token']
            ]);
        }
        return [
            'code' => 60204,
            'message'=>'Account and password are incorrect.'
        ];
    }
}