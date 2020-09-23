<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/9/5 0005
 * Time: 16:28
 */

namespace App\Demo\Controller;

use App\Demo\Logic\DemoLogic;
use Library\Virtual\Controller\AbstractController;

class DemoController extends AbstractController
{
    public function testMysql()
    {
        return ['data' => (new DemoLogic())->testMysql()];
    }
}