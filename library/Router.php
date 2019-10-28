<?php

namespace Library;

use Library\Entity\Swoole\EntitySwooleWebSever;
use Library\Object\Web\RouteObject;
use Swoole\Coroutine;

/**
 * Created by PhpStorm.
 * User: ZhongHao-Zh
 * Date: 2019/10/20
 * Time: 16:55
 */

/**
 * Class Router
 * @package Library
 */
class Router
{
    /**
     * 路由对象
     * @var array $routePool
     */
    private static $routePool = [];

    /**
     * 路由规则对象
     * @var array $routerPool
     */
    private static $routerPool = [];

    /**
     * 初始化Router类
     */
    public static function instanceStart()
    {
        $handler = opendir(dirname(__FILE__) . '/../route');
        while (($fileName = readdir($handler)) !== false) {
            if ($fileName != "." && $fileName != "..") {
                $fileData = require dirname(__FILE__) . '/../route/' . $fileName;
                self::$routerPool = array_merge(self::$routerPool, $fileData);
            }
        }
        closedir($handler);
    }

    /**
     * 获取当前处理的匹配出的路由
     * @return RouteObject
     */
    public static function getRouteInstance(): RouteObject
    {
        $workerId = EntitySwooleWebSever::getInstance()->worker_id;
        $cid = Coroutine::getuid();
        return static::$routePool[$workerId][$cid]?:(new RouteObject());
    }

    /**
     * 删除当前路由对象
     */
    public static function delRouteInstance(int $workerId = -1)
    {
        if ($workerId == -1) {
            $cid = Coroutine::getuid();
            $workerId = EntitySwooleWebSever::getInstance()->worker_id;
            unset(static::$routePool[$workerId][$cid]);
        } else {
            unset(static::$routePool[$workerId]);
        }
    }

    /**
     * 路由
     * @param string $requestUrl
     * @return RouteObject
     */
    public static function router(string $requestUrl): RouteObject
    {
        $route = self::$routerPool[$requestUrl] ?? null;
        if (is_null($route)) {
            $requestUrl = trim($requestUrl, '/');
            $requestUrlArray = explode('/', $requestUrl);
            $requestUrlArray[0] = isset($requestUrlArray[0]) && $requestUrlArray[0] ? $requestUrlArray[0] : 'Api';
            $requestUrlArray[1] = isset($requestUrlArray[1]) && $requestUrlArray[1] ? $requestUrlArray[1] : 'Index';
            $requestUrlArray[2] = isset($requestUrlArray[2]) && $requestUrlArray[2] ? $requestUrlArray[2] : 'index';

            $routerObject = new RouteObject();
            $routerObject->setProject($requestUrlArray[0]);
            $routerObject->setController("\\App\\{$requestUrlArray[0]}\\Controller\\{$requestUrlArray[1]}Controller");
            $routerObject->setMethod($requestUrlArray[2]);

            static::$routePool[EntitySwooleWebSever::getInstance()->worker_id][Coroutine::getuid()] = $routerObject;

            return $routerObject;
        } else {
            $requestUrlArray = explode('@', $route);

            $routerObject = new RouteObject();
            $routerObject->setProject((explode('\\', $requestUrlArray[0]))[2]);
            $routerObject->setController($requestUrlArray[0]);
            $routerObject->setMethod($requestUrlArray[1]);

            static::$routePool[EntitySwooleWebSever::getInstance()->worker_id][Coroutine::getuid()] = $routerObject;

            return $routerObject;
        }
    }

}