<?php
/**
 * Created by PhpStorm.
 * User: ZhongHao-Zh
 * Date: 2019/10/20
 * Time: 16:55
 */

namespace Library;

use Library\Entity\Swoole\EntitySwooleServer;
use Library\Object\RouteObject;
use Swoole\Coroutine;

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
     * @param string $lockFileName
     */
    public static function instanceStart(string $lockFileName = '')
    {
        if (!empty($lockFileName) && file_exists(dirname(__FILE__) . '/../route/' . $lockFileName)) {
            $fileData = require dirname(__FILE__) . '/../route/' . $lockFileName;
            $routerData = self::analysisRouter($fileData);
            $routerData && self::$routerPool = array_merge(self::$routerPool, $routerData);
        } else {
            $handler = opendir(dirname(__FILE__) . '/../route');
            while (($fileName = readdir($handler)) !== false) {
                if ($fileName != "." && $fileName != "..") {
                    $fileData = require dirname(__FILE__) . '/../route/' . $fileName;
                    $routerData = self::analysisRouter($fileData);
                    $routerData && self::$routerPool = array_merge(self::$routerPool, $routerData);
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
    private static function analysisRouter(array $fileData, string $baseRoute = '')
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
     * @return RouteObject
     */
    public static function getRouteInstance(): RouteObject
    {
        if (EntitySwooleServer::getInstance()) {
            $workerId = EntitySwooleServer::getInstance()->worker_id;
            $cid = Coroutine::getuid();
            return static::$routePool[$workerId][$cid] ?? (new RouteObject());
        } else {
            return static::$routePool[0][0] ?? (new RouteObject());
        }
    }

    /**
     * 删除当前路由对象
     * @param int $workerId
     */
    public static function delRouteInstance(int $workerId = -1)
    {
        if ($workerId == -1) {
            $cid = Coroutine::getuid();
            $workerId = EntitySwooleServer::getInstance()->worker_id;
            unset(static::$routePool[$workerId][$cid]);
        } else {
            unset(static::$routePool[$workerId]);
        }
    }

    /**
     * 路由
     * @param string $requestUrl
     * @param string $type
     * @return RouteObject
     */
    public static function router(string $requestUrl, string $type = 'Controller'): RouteObject
    {
        if ($type != 'Controller') {
            $type = 'Task';
        }
        $route = self::$routerPool[$requestUrl] ?? null;
        if (is_null($route)) {
            $requestUrl = trim($requestUrl, '/');
            $requestUrlArray = explode('/', $requestUrl);
            $requestUrlArray[0] = (isset($requestUrlArray[0]) && $requestUrlArray[0]) ? ucfirst($requestUrlArray[0]) : 'Index';
            $requestUrlArray[1] = (isset($requestUrlArray[1]) && $requestUrlArray[1]) ? ucfirst($requestUrlArray[1]) : 'Index';
            $requestUrlArray[2] = (isset($requestUrlArray[2]) && $requestUrlArray[2]) ? $requestUrlArray[2] : 'index';

            $routerObject = new RouteObject();
            $routerObject->setProject($requestUrlArray[0]);
            $routerObject->setController("\\App\\{$requestUrlArray[0]}\\{$type}\\{$requestUrlArray[1]}{$type}");
            $routerObject->setMethod($requestUrlArray[2]);
            $routerObject->setRoute($requestUrl);

            if (in_array($type, ['Controller'])) {
                if (EntitySwooleServer::getInstance()) {
                    static::$routePool[EntitySwooleServer::getInstance()->worker_id][Coroutine::getuid()] = $routerObject;
                } else {
                    static::$routePool[0][0] = $routerObject;
                }
            }
            return $routerObject;
        } else {
            $requestUrlArray = explode('@', $route);

            $routerObject = new RouteObject();
            $routerObject->setProject((explode('\\', $requestUrlArray[0]))[2]);
            $routerObject->setController($requestUrlArray[0]);
            $routerObject->setMethod($requestUrlArray[1]);
            $routerObject->setRoute($requestUrl);

            if (in_array($type, ['Controller'])) {
                if (EntitySwooleServer::getInstance()) {
                    static::$routePool[EntitySwooleServer::getInstance()->worker_id][Coroutine::getuid()] = $routerObject;
                } else {
                    static::$routePool[0][0] = $routerObject;
                }
            }
            return $routerObject;
        }
    }

}