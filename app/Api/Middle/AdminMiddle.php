<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/11/15
 * Time: 17:09
 */

namespace App\Api\Middle;

use Library\Virtual\Middle\AbstractMiddleWare;

class AdminMiddle extends AbstractMiddleWare
{
    /**
     * @throws \Exception
     */
    public function login()
    {
        $this
            ->setRequestField(['username', 'password'])
            ->takeMiddleData();
    }
}
