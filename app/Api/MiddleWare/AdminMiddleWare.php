<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/11/15
 * Time: 17:09
 */

namespace App\Api\MiddleWare;

use Library\Virtual\MiddleWare\AbstractMiddleWare;

class AdminMiddleWare extends AbstractMiddleWare
{
    /**
     * @throws \Exception
     */
    public function login()
    {
        $this->setRequestField([
            'username',
            'password'
        ])->takeMiddleData();
    }
}
