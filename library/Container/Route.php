<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/10/22
 * Time: 16:43
 */

namespace Library\Container;

/**
 * Class RouteObject
 * @package Library\Object\Web
 */
class Route
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
     * @var string $route 具体匹配出的路由
     */
    private $route = '';

    /**
     * @return string
     */
    public function getProject(): string
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
    public function getController(): string
    {
        return $this->controller;
    }

    /**
     * @return string
     */
    public function getTask(): string
    {
        return $this->controller;
    }

    /**
     * @return string
     */
    public function getRoute(): string
    {
        return $this->route;
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
    public function getMethod(): string
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

    /**
     * @param $route
     */
    public function setRoute($route)
    {
        $this->route = $route;
    }
}