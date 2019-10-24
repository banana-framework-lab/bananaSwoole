<?php

namespace Library;

use Library\Entity\Swoole\EntitySwooleWebSever;
use Library\Object\RouterObject;
use Swoole\Coroutine;

/**
 * Created by PhpStorm.
 * User: ZhongHao-Zh
 * Date: 2019/10/20
 * Time: 16:55
 */
class Router
{
    /**
     * @var array $routePool
     */
    public static $routePool = [];

    /**
     * @var array $routeObjectPool
     */
    public static $routeObjectPool = [];

    /**
     * 初始化Router类
     */
    public static function instanceStart()
    {
        if (!self::$routePool) {
            $handler = opendir(dirname(__FILE__) . '/../route');
            while (($fileName = readdir($handler)) !== false) {
                if ($fileName != "." && $fileName != "..") {
                    self::$routePool += require dirname(__FILE__) . '/../route/' . $fileName;
                }
            }
            closedir($handler);
        }
    }

    /**
     * 删除路由对象
     */
    public static function delInstance()
    {
        foreach (static::$routePool as & $routeInstance) {
            unset($routeInstance);
        }
        foreach (static::$routeObjectPool as & $routeObjectInstance) {
            unset($routeObjectInstance);
        }
    }

    /**
     * 回收指定协程内的对象
     */
    public static function recoverInstance()
    {
        $cid = Coroutine::getuid();
        $workId = EntitySwooleWebSever::getInstance()->worker_id;
        unset(static::$routeObjectPool[$workId][$cid]);
    }

    /**
     * 路由
     * @param string $requestUrl
     * @return RouterObject
     */
    public static function route(string $requestUrl)
    {
        $v = self::$routePool[$requestUrl] ?? null;
        if (is_null($v)) {
            $requestUrl = trim($requestUrl, '/');
            $requestUrlArray = explode('/', $requestUrl);
            $requestUrlArray[0] = isset($requestUrlArray[0]) && $requestUrlArray[0] ? $requestUrlArray[0] : 'Api';
            $requestUrlArray[1] = isset($requestUrlArray[1]) && $requestUrlArray[1] ? $requestUrlArray[1] : 'Index';
            $requestUrlArray[2] = isset($requestUrlArray[2]) && $requestUrlArray[2] ? $requestUrlArray[2] : 'index';

            $routerObject = new RouterObject();
            $routerObject->setProject($requestUrlArray[0]);
            $routerObject->setController("\\App\\{$requestUrlArray[0]}\\Controller\\{$requestUrlArray[1]}Controller");
            $routerObject->setMethod($requestUrlArray[2]);

            self::$routeObjectPool[EntitySwooleWebSever::getInstance()->worker_id][Coroutine::getuid()] = $routerObject;

            return $routerObject;
        } else {
            $requestUrlArray = explode('@', $v);

            $routerObject = new RouterObject();
            $routerObject->setProject((explode('\\', $requestUrlArray[0]))[2]);
            $routerObject->setController($requestUrlArray[0]);
            $routerObject->setMethod($requestUrlArray[1]);

            self::$routeObjectPool[EntitySwooleWebSever::getInstance()->worker_id][Coroutine::getuid()] = $routerObject;

            return $routerObject;
        }
    }

}