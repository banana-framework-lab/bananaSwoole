<?php
namespace Library\Object;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/10/22
 * Time: 16:43
 */
class RouterObject
{

    /**
     * @var string 路由的控制器
     */
    private $controller = '';

    /**
     * @var string 路由的函数
     */
    private $method = '';

    /**
     * @return string
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * @param $controller
     */
    public function setController($controller)
    {
        $this->controller = $controller;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param $method
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }
}