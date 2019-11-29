<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/11/22
 * Time: 20:25
 */

namespace Library\Virtual\Server;

/**
 * Class AbstractFpmServer
 * @package Library\Virtual\Server
 */
abstract class AbstractFpmServer
{
    /**
     * onRequest执行入口
     */
    abstract public function request();
}