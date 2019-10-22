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
     * @var string $project 路由的项目
     */
    private $project = '';

    /**
     * @var string $controller 路由的控制器
     */
    private $controller = '';

    /**
     * @var string $method 路由的函数
     */
    private $method = '';

    /**
     * @return string
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * @param $project
     */
    public function setProject($project)
    {
        $this->project = $project;
    }

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