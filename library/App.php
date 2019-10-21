<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/9/5 0005
 * Time: 17:02
 */

namespace Library;

use Library\Entity\Model\Cache\EntityRedis;
use Library\Entity\Model\DataBase\EntityMongo;
use Library\Entity\Model\DataBase\EntityMysql;
use Library\Helper\ResponseHelper;
use Library\Entity\Swoole\EntitySwooleRequest;
use Library\Virtual\Middle\AbstractMiddleWare;

class App
{
    /**
     * 初始化
     */
    public static function init()
    {
        //若是重启先删除单例实体对象
        EntitySwooleRequest::delInstance();
        EntityMysql::delInstance();
        EntityMongo::delInstance();
        EntityRedis::delInstance();

        // 数据库初始化
        EntityMysql::instanceStart();
        EntityMongo::instanceStart();

        // Redis初始化
        EntityRedis::instanceStart();

        // Router初始化
        Router::instanceStart();  
    }

    /**
     * 执行入口
     * @param $request
     * @return string
     */
    public static function run($request)
    {
        //初始化请求实体类
        EntitySwooleRequest::setInstance($request);

        //路由
        $routeArr = self::route($request->server['request_uri']);

        //初始化方法
        $methodName = $routeArr['method'];
        $controllerClass = $routeArr['controller'];

        //初始化请求数据
        $getData = $request->get ?: [];
        $postData = $request->post ?: [];
        $requestData = array_merge($getData, $postData);

        //初始化请求中间件
        try {
            $middleClass = str_replace("Controller", "Middle", $controllerClass);;
            /* @var AbstractMiddleWare $middleWare */
            if (class_exists($middleClass)) {
                $middleWare = new $middleClass($requestData);
                if (method_exists($middleWare, $methodName)) {
                    $middleWare->$methodName();
                    $requestData = $middleWare->takeMiddleData();
                }
            }
        } catch (\Exception $e) {
            return ResponseHelper::responseFailed(['msg' => $e->getMessage()]);
        }

        //初始化控制器
        if (class_exists($controllerClass)) {
            $controller = new $controllerClass($requestData);
            if (method_exists($controller, $methodName)) {
                return $controller->$methodName();
            } else {
                return ResponseHelper::responseFailed(['msg' => "找不到{$methodName}"]);
            }
        } else {
            return ResponseHelper::responseFailed(['msg' => "找不到{$controllerClass}"]);
        }
    }

    /**
     * @param string $requestUrl
     * @return array
     */
    private static function route($requestUrl)
    {
        $v = Router::$routePool[$requestUrl] ?? null;
        if (is_null($v)) {
            $requestUrl = trim($requestUrl, '/');
            $requestUrlArray = explode('/', $requestUrl);
            $requestUrlArray[0] = $requestUrlArray[0] ?: 'Api';
            $requestUrlArray[1] = $requestUrlArray[1] ?: 'Index';
            $requestUrlArray[2] = $requestUrlArray[2] ?: 'index';
            return [
                'controller' => "\\\{$requestUrlArray[0]}\\Controller\\{$requestUrlArray[1]}",
                'method' => $requestUrlArray[2]
            ];
        } else {
            $requestUrlArray = explode('@', $v);
            return [
                'controller' => $requestUrlArray[0],
                'method' => $requestUrlArray[1]
            ];
        }
    }
}