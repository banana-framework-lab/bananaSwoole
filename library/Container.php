<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/12/23
 * Time: 15:40
 */

namespace Library;

use Exception;
use Library\Container\Config;
use Library\Container\Pool\MysqlPool;
use Library\Container\Pool\RedisPool;
use Library\Container\Request;
use Library\Container\Response;
use Library\Container\Router;
use Library\Container\SwooleServer;
use Swoole\WebSocket\Server;

/**
 * Class Validate
 * @package Library
 */
class Container
{
    /**
     * 配置对象
     * @var Config $config
     */
    static private $config;

    /**
     * 设置配置对象
     */
    static public function setConfig()
    {
        self::$config = new Config();
    }

    /**
     * 获取配置对象
     * @return Config
     */
    static public function getConfig()
    {
        return self::$config;
    }

    /**
     * Swoole的Server对象
     * @var Server $swooleServer
     */
    static private $swooleServer;

    /**
     * 设置SwooleServer对象
     * @param string $serverConfigIndex
     */
    static public function setSwooleSever(string $serverConfigIndex)
    {
        self::$swooleServer = (new SwooleServer($serverConfigIndex))->getSwooleServer();
    }

    /**
     * 获取swooleServer对象
     * @return Server
     */
    static public function getSwooleServer()
    {
        return self::$swooleServer;
    }

    /**
     * @var Request $request
     */
    static private $request;

    /**
     * 设置请求对象
     */
    static public function setRequest()
    {
        self::$request = new Request();
    }

    /**
     * 获取请求对象
     * @return Request
     */
    static public function getRequest()
    {
        return self::$request;
    }

    /**
     * @var Response 请求对象
     */
    static private $response;

    /**
     * 设置请求对象
     */
    static public function setResponse()
    {
        self::$request = new Response();
    }

    /**
     * 获取请求对象
     * @return Response
     */
    static public function getResponse()
    {
        return self::$response;
    }

    /**
     * 路由对象
     * @var Router $router
     */
    static private $router;

    /**
     * 设置路由对象
     */
    static public function setRouter()
    {
        self::$router = new Router();
    }

    /**
     * 返回路由对象
     * @return Router
     */
    static public function getRouter()
    {
        return self::$router;
    }

    /**
     * mysql连接池
     * @var MysqlPool $mysqlPool
     */
    static private $mysqlPool;

    /**
     * 设置mysql连接池
     * @param string $configName
     * @throws Exception
     */
    static public function setMysqlPool($configName = 'server')
    {
        self::$mysqlPool = new MysqlPool($configName);
    }

    /**
     * 返回mysql连接池
     * @return MysqlPool
     */
    static public function getMysqlPool()
    {
        return self::$mysqlPool;
    }

    /**
     * redis连接池
     * @var RedisPool $redisPool
     */
    static private $redisPool;

    /**
     * 设置mysql连接池
     * @param string $configName
     * @throws Exception
     */
    static public function setRedisPool($configName = 'server')
    {
        self::$redisPool = new RedisPool($configName);
    }

    /**
     * 返回mysql连接池
     * @return RedisPool
     */
    static public function getRedisPool()
    {
        return self::$redisPool;
    }
}