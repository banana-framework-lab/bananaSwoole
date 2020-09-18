<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/11/15
 * Time: 17:09
 */

namespace App\Demo\MiddleWare;

use Exception;
use Library\Virtual\MiddleWare\AbstractMiddleWare;

class DemoMiddleWare extends AbstractMiddleWare
{
    /**
     * @throws Exception
     */
    public function login()
    {
        $this->setRequestField([
            'username',
            'password'
        ])->setRequestErrMsg([
            'username' => '用户名',
            'password' => '密码',
        ])->setRequestDefault([
            'username' => 'admin',
            'password' => '123456',
        ])->setRequestAfter([
            'username' => function ($data) {
                return $data['username'];
            },
            'password' => function ($data) {
                return $data['password'];
            },
        ])->takeMiddleData();
    }
}
