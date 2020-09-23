<?php
/**
 * Created by PhpStorm.
 * User: ZhongHao-Zh
 * Date: 2019/10/26
 * Time: 20:15
 */

namespace App\Demo\Logic;

use App\Demo\Model\DatabaseModel\DemoMysqlModel;

class DemoLogic
{
    public function testMysql()
    {
        return [1];
       return (new DemoMysqlModel())->getList();
    }
}