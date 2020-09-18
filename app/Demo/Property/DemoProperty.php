<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/11/15
 * Time: 20:06
 */

namespace App\Demo\Property;

use Exception;
use Library\Virtual\Property\AbstractProperty;

/**
 * Class DemoProperty
 * @package App\Api\Property
 */
class DemoProperty extends AbstractProperty
{
    public $id;

    public $msg;

    /**
     * 设置属性
     * 可以默认写法
     * public function setProperty(array $params)
     * {
     *    return $this->__setProperty($params);
     * }
     * @param array $params
     * @return $this
     * @throws Exception
     */
    public function setProperty(array $params)
    {
        return $this->__setProperty($params);
    }
}