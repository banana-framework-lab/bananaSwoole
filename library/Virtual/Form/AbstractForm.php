<?php
/**
 * Created by PhpStorm.
 * User: ZhongHao-Zh
 * Date: 2020/1/11
 * Time: 15:26
 */

namespace Library\Virtual\Form;


abstract class AbstractForm
{
    /**
     * @return mixed
     */
    abstract function filteringRule() ;
}