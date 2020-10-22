<?php
/**
 * Created by PhpStorm.
 * User: ZhongHao-Zh
 * Date: 2019/10/26
 * Time: 20:18
 */

namespace App\Demo\Model\HttpModel;

use Library\Virtual\Model\HttpModel\AbstractHttpModel;


class DemoHttpModel extends AbstractHttpModel
{
    public function getData()
    {
        $this->getCurl('http://www.baidu.com');
    }
}