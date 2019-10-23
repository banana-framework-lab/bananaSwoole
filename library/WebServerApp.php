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
use Library\Object\RouterObject;
use Library\Virtual\Middle\AbstractMiddleWare;
use Swoole\Http\Request as SwooleRequest;

class WebServerApp
{
    /**
     * 初始化
     */
    public static function init()
    {
        //若是重启先删除单例实体对象
        EntitySwooleRequest::delInstance();
        Router::delInstance();
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

        //开启php调试模式
        if (Config::get('app.debug')) {
            ini_set('display_errors', 'On');
            error_reporting(E_ALL);
        }
    }

    /**
     * 执行入口
     * @param SwooleRequest $request
     * @return string
     */
    public static function run(SwooleRequest $request)
    {
        //初始化请求实体类
        EntitySwooleRequest::setInstance($request);

        /* @var RouterObject $routeObject */
        $routeObject = Router::route($request->server['request_uri']);

        //初始化方法
        $methodName = $routeObject->getMethod();
        $controllerClass = $routeObject->getController();

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
                $result = $controller->$methodName();
            } else {
                $result = ResponseHelper::responseFailed(['msg' => "找不到{$methodName}"]);
            }
        } else {
            $result = ResponseHelper::responseFailed(['msg' => "找不到{$controllerClass}"]);
        }

        EntitySwooleRequest::recoverInstance();
        Router::recoverInstance();
        return $result;
    }
}