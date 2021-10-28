<?php
/**
 * Created by PhpStorm.
 * User: ZhongHao-Zh
 * Date: 2019/10/20
 * Time: 16:55
 */

namespace Library\Container\Instance;

use Library\Container;
use Library\Container\Route;
use Library\Container\TaskRoute;

/**
 * Class Router
 * @package Library
 */
class TaskRouterMap
{
    /**
     * 路由对象
     * @var array $pool
     */
    private $pool = [];

    /**
     * 获取当前处理的匹配出的路由
     * @param int $workerId
     * @param int $cId
     * @return Route
     */
    public function getRoute(int $workerId = 0, int $cId = 0): Route
    {
        return $this->pool[$workerId][$cId] ?? (new Route());
    }

    /**
     * 删除当前路由对象
     * @param int $workerId
     * @param int $cId
     */
    public function delRoute(int $workerId = 0, int $cId = 0)
    {
        unset($this->pool[$workerId][$cId]);
    }

    /**
     * 路由
     * @param string $requestUrl
     * @param int $workerId
     * @param int $cId
     * @return TaskRoute
     */
    public function taskRouter(string $requestUrl, int $workerId = 0, int $cId = 0): TaskRoute
    {
        $requestUrl = trim($requestUrl, '/');
        $requestUrlArray = explode('/', $requestUrl);
        $requestUrlArray[0] = (isset($requestUrlArray[0]) && $requestUrlArray[0]) ? ucfirst($requestUrlArray[0]) : 'Index';
        $requestUrlArray[1] = (isset($requestUrlArray[1]) && $requestUrlArray[1]) ? ucfirst($requestUrlArray[1]) : 'Index';
        $requestUrlArray[2] = (isset($requestUrlArray[2]) && $requestUrlArray[2]) ? $requestUrlArray[2] : 'index';

        $routerObject = new TaskRoute();
        $routerObject->setProject($requestUrlArray[0]);
        $routerObject->setTask("\\App\\{$requestUrlArray[0]}\\Task\\{$requestUrlArray[1]}Task");
        $routerObject->setMethod($requestUrlArray[2]);
        $routerObject->setRoute($requestUrl);

        $this->pool[$workerId][$cId] = $routerObject;

        //根据路由判断是否加载过Common文件
        Container::loadCommonFile($routerObject->getProject());
        return $routerObject;
    }

}