<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/10/22
 * Time: 16:43
 */

namespace Library\Container;

/**
 * Class TaskRoute
 * @package Library\Object\Web
 */
class TaskRoute
{
    /**
     * @var string $project 路由的项目
     */
    private $project = '';

    /**
     * @var string $task 路由的控制器
     */
    private $task = '';

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
    public function getTask(): string
    {
        return $this->task;
    }

    /**
     * @return string
     */
    public function getRoute(): string
    {
        return $this->route;
    }

    /**
     * @param $task
     */
    public function setTask($task)
    {
        $this->task = $task;
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