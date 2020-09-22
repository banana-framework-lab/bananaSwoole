<?php
/**
 * Created by PhpStorm.
 * User: ZhongHao-Zh
 * Date: 2019/10/20
 * Time: 16:55
 */

namespace Library\Container;

use Library\Common;
use Library\Object\RouteObject;

/**
 * Class Router
 * @package Library
 */
class Router
{
    /**
     * 路由对象
     * @var array $pool
     */
    private $pool = [];

    /**
     * 路由规则对象
     * @var array $routerPool
     */
    private $routerPool = [];

    /**
     * 初始化Router类
     * @param string $lockFileName
     */
    public function __construct(string $lockFileName = '')
    {
        if (!empty($lockFileName) && file_exists(dirname(__FILE__) . '/../../route/' . $lockFileName)) {
            $fileData = require dirname(__FILE__) . '/../../route/' . $lockFileName;
            $routerData = $this->analysisRouter($fileData);
            $routerData && $this->routerPool = array_merge($this->routerPool, $routerData);
        } else {
            $handler = opendir(dirname(__FILE__) . '/../../route');
            while (($fileName = readdir($handler)) !== false) {
                if ($fileName != "." && $fileName != "..") {
                    $fileData = require dirname(__FILE__) . '/../../route/' . $fileName;
                    $routerData = $this->analysisRouter($fileData);
                    $routerData && $this->routerPool = array_merge($this->routerPool, $routerData);
                }
            }
            closedir($handler);
        }
    }

    /**
     * 解析路由
     * @param array $fileData
     * @param string $baseRoute
     * @return array
     */
    private function analysisRouter(array $fileData, string $baseRoute = '')
    {
        $routerData = [];
        $originBaseRoute = $baseRoute;
        foreach ($fileData as $key => $value) {
            if (is_array($value)) {
                if ($originBaseRoute) {
                    $baseRoute .= "/{$key}";
                } else {
                    $baseRoute = "/{$key}";
                }
            }
            if (is_string($value)) {
                $routerData["{$baseRoute}/{$key}"] = $value;
            } else {
                $routerData = $routerData + self::analysisRouter($value, $baseRoute);
            }
        }
        return $routerData;
    }

    /**
     * 获取当前处理的匹配出的路由
     * @param int $workerId
     * @param int $cId
     * @return RouteObject
     */
    public function getRoute(int $workerId = 0, int $cId = 0): RouteObject
    {
        return $this->pool[$workerId][$cId] ?? (new RouteObject());
    }

    /**
     * 删除当前路由对象
     * @param int $workerId
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
     * @return RouteObject
     */
    public function controllerRouter(string $requestUrl, int $workerId = 0, int $cId = 0): RouteObject
    {
        $route = $this->routerPool[$requestUrl] ?? null;
        if (is_null($route)) {
            $requestUrl = trim($requestUrl, '/');
            $requestUrlArray = explode('/', $requestUrl);
            $requestUrlArray[0] = (isset($requestUrlArray[0]) && $requestUrlArray[0]) ? ucfirst($requestUrlArray[0]) : 'Index';
            $requestUrlArray[1] = (isset($requestUrlArray[1]) && $requestUrlArray[1]) ? ucfirst($requestUrlArray[1]) : 'Index';
            $requestUrlArray[2] = (isset($requestUrlArray[2]) && $requestUrlArray[2]) ? $requestUrlArray[2] : 'index';

            $routerObject = new RouteObject();
            $routerObject->setProject($requestUrlArray[0]);
            $routerObject->setController("\\App\\{$requestUrlArray[0]}\\Controller}\\{$requestUrlArray[1]}Controller}");
            $routerObject->setMethod($requestUrlArray[2]);
            $routerObject->setRoute($requestUrl);
        } else {
            $requestUrlArray = explode('@', $route);

            $routerObject = new RouteObject();
            $routerObject->setProject((explode('\\', $requestUrlArray[0]))[2]);
            $routerObject->setController($requestUrlArray[0]);
            $routerObject->setMethod($requestUrlArray[1]);
            $routerObject->setRoute($requestUrl);
        }

        $this->pool[$workerId][$cId] = $routerObject;

        //根据路由判断是否加载过Common文件
        Common::loadCommonFile($routerObject->getProject());
        return $routerObject;
    }

}