<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/9/5 0005
 * Time: 17:02
 */

namespace Library\App\Server;

use Library\Config;
use Library\Entity\MessageQueue\EntityRabbit;
use Library\Entity\Model\Cache\EntityRedis;
use Library\Entity\Model\DataBase\EntityMongo;
use Library\Entity\Model\DataBase\EntityMysql;
use Library\Exception\WebException;
use Library\Helper\ResponseHelper;
use Library\Object\RouteObject;
use Library\Router;
use Library\Virtual\Middle\AbstractMiddleWare;
use Library\Virtual\Server\AbstractFpmServer;
use Throwable;


/**
 * Class DefaultWebSocketServer
 * @package Library
 */
class DefaultFpmServer extends AbstractFpmServer
{
    /**
     * onRequest执行入口
     */
    public function request()
    {
        try {
            // 路由配置
            Router::instanceStart();

            // mysql数据库初始化
            EntityMysql::instanceStart();

            // mongo数据库初始化
            EntityMongo::instanceStart();

            // Redis缓存初始化
            EntityRedis::instanceStart();

            // rabbitMq初始化
            EntityRabbit::instanceStart();

        } catch (Throwable $e) {
            var_dump($e->getMessage(), $e->getTraceAsString());
            return;
        }

        $pathInfo = trim($_SERVER['PATH_INFO'], '/');

        $pathInfo = ($pos = strripos($pathInfo, '.html')) ? substr($pathInfo, 0, $pos) : $pathInfo;

        $pathInfoArray = explode('/', $pathInfo);

        $projectName = $pathInfoArray[0] ? ucfirst($pathInfoArray[0]) : 'Index';
        $controllerName = $pathInfoArray[1] ? ucfirst($pathInfoArray[1]) : 'Index';
        $methodName = $pathInfoArray[2] ? ucfirst($pathInfoArray[2]) : 'index';

        /* @var RouteObject $routeObject */
        $routeObject = Router::router("/{$projectName}/{$controllerName}/{$methodName}");

        //初始化方法
        $methodName = $routeObject->getMethod();
        $controllerClass = $routeObject->getController();

        //初始化请求数据
        $requestData = $_REQUEST;

        //初始化请求中间件
        try {
            $middleClass = str_replace("Controller", "Middle", $controllerClass);;
            /* @var AbstractMiddleWare $middleWare */
            if (method_exists($middleClass, $methodName)) {
                $middleWare = new $middleClass($requestData);
                $middleWare->$methodName();
                $requestData = $middleWare->takeMiddleData();
            }
        } catch (Throwable $e) {
            ResponseHelper::json(['code' => 10000, 'message' => $e->getMessage()]);
            return;
        }

        //初始化控制器
        try {
            if (class_exists($controllerClass)) {
                $controller = new $controllerClass($requestData);
                if (method_exists($controller, $methodName)) {
                    $returnData = $controller->$methodName();
                    if ($returnData) {
                        ResponseHelper::json($returnData);
                    }
                } else {
                    if (Config::get('app.debug')) {
                        ResponseHelper::json(['code' => 10000, 'message' => "找不到方法名：{$methodName}"]);
                    } else {
                        exit(404);
                    }
                }
            } else {
                if (Config::get('app.debug')) {
                    ResponseHelper::json(['code' => 10000, 'message' => "找不到控制器：{$controllerClass}"]);
                } else {
                    exit(404);
                }
            }
        } catch (WebException $webE) {
            ResponseHelper::json([
                'code' => $webE->getCode(),
                'message' => $webE->getMessage()
            ]);
        } catch (Throwable $e) {
            if (Config::get('app.debug')) {
                var_dump($e->getMessage(), $e->getTraceAsString());
                exit();
            } else {
                exit(500);
            }
        }
    }
}