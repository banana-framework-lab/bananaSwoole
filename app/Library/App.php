<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/9/5 0005
 * Time: 17:02
 */

namespace App\Library;

use App\Library\Entity\Model\DataBase\Mysql;
use App\Library\Entity\Response;
use App\Library\Entity\Swoole\Request;
use App\Library\Virtual\Middle\AbstractMiddleWare;
use Illuminate\Database\Capsule\Manager;

class App
{
    /**
     * 初始化
     */
    public static function init()
    {
        //若是热重启先删除数据
        Request::recoverInstance();
        Mysql::recoverDBInstance();

        // 数据库初始化
        self::initDatabase();


    }

    /**
     * 执行入口
     * @param $request
     * @return string
     */
    public static function run($request)
    {
        //初始化请求实体类
        Request::setReqInstance($request);

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
            return Response::instance()->responseFailed(['msg' => $e->getMessage()]);
        }

        //初始化控制器
        if (class_exists($controllerClass)) {
            $controller = new $controllerClass($requestData);
            if (method_exists($controller, $methodName)) {
                return $controller->$methodName();
            } else {
                return Response::instance()->responseFailed(['msg' => "找不到{$methodName}"]);
            }
        } else {
            return Response::instance()->responseFailed(['msg' => "找不到{$controllerClass}"]);
        }
    }

    /**
     * @param string $requestUrl
     * @return array
     */
    private static function route($requestUrl)
    {
        $routeArr = require dirname(__FILE__) . '/../Api/Route/route.php';;
        $v = $routeArr[$requestUrl];
        if (is_null($v)) {
            $requestUrl = trim($requestUrl, '/');
            $requestUrlArray = explode('/', $requestUrl);
            $requestUrlArray[0] = $requestUrlArray[0] ?: 'Api';
            $requestUrlArray[1] = $requestUrlArray[1] ?: 'Index';
            $requestUrlArray[2] = $requestUrlArray[2] ?: 'index';
            return [
                'controller' => "\\App\\{$requestUrlArray[0]}\\Controller\\{$requestUrlArray[1]}",
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

    /*
     * 初始化数据库
     */
    private static function initDatabase()
    {
        $capsule = new Manager;

        //设置数据库的配置
        $capsule->addConnection(IS_SERVER ? DB_LIST['server'] : DB_LIST['local']);

        // 使得数据库对象全局可用
        $capsule->setAsGlobal();

        //初始化mysql全局对象
        Mysql::setDBInstance($capsule);

        //设置可用Eloquent
        $capsule->bootEloquent();

        //非服务器下开启日志
        if (!IS_SERVER) {
            Mysql::connection()->enableQueryLog();
        }
    }
}