<?php
/**
 * Created by PhpStorm.
 * User: ZhongHao-Zh
 * Date: 2019/10/26
 * Time: 20:15
 */

namespace App\Demo\Logic;


use App\Api\Model\DatabaseModel\DemoMysqlModel;

class DemoLogic
{
    /**
     * @return array
     */
    public function demoLogic()
    {
        return ['msg' => '成功执行DemoLogic的demoLogic方法'];
    }

    /**
     *
     */
    public function demoLogicForModel()
    {
       return (new DemoMysqlModel())->demo();
    }
}