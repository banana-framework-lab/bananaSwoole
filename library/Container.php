<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/12/23
 * Time: 15:40
 */

namespace Library;

use Exception;
use Library\Container\Instance\ChannelRouterMap;
use Library\Container\Instance\Config;
use Library\Container\Instance\Log;
use Library\Container\Instance\TaskRouterMap;
use Library\Container\Pool\MongoPool;
use Library\Container\Pool\MysqlPool;
use Library\Container\Pool\RabbitMQPool;
use Library\Container\Pool\RedisPool;
use Library\Container\Instance\Request;
use Library\Container\Instance\Response;
use Library\Container\Instance\RouterMap;
use Library\Container\Instance\Server;
use Swoole\WebSocket\Server as SwooleServer;

/**
 * Class Validate
 * @package Library
 */
class Container
{
    /**
     * 服务器默认配置下标
     * @var string $serverConfigIndex
     */
    static private $serverConfigIndex = '';

    /**
     * 设置配置对象
     * @param string $serverConfigIndex
     */
    static public function setServerConfigIndex(string $serverConfigIndex)
    {
        self::$serverConfigIndex = $serverConfigIndex;
    }

    /**
     * 获取配置对象
     * @return string
     */
    static public function getServerConfigIndex(): string
    {
        return self::$serverConfigIndex;
    }

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
    static public function getConfig(): Config
    {
        return self::$config;
    }

    /**
     * Server对象
     * @var Server $swooleServer
     */
    static private $swooleServer;

    /**
     * 设置Server对象
     * @param string $serverConfigIndex
     */
    static public function setSever(string $serverConfigIndex)
    {
        self::$swooleServer = (new Server($serverConfigIndex));
    }

    /**
     * 获取Server对象
     * @return Server
     */
    static public function getServer(): Server
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
    static public function getRequest(): Request
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
        self::$response = new Response();
    }

    /**
     * 获取请求对象
     * @return Response
     */
    static public function getResponse(): Response
    {
        return self::$response;
    }

    /**
     * 路由对象
     * @var RouterMap $router
     */
    static private $router;

    /**
     * 任务路由对象
     * @var TaskRouterMap $taskRouter
     */
    static private $taskRouter;

    /**
     * websocket路由对象
     * @var ChannelRouterMap $taskRouter
     */
    static private $channelRouter;

    /**
     * 设置路由对象
     */
    static public function setRouter()
    {
        self::$router = new RouterMap();
    }

    /**
     * 返回路由对象
     * @return RouterMap
     */
    static public function getRouter(): RouterMap
    {
        return self::$router;
    }

    /**
     * 设置task路由对象
     */
    static public function setTaskRouter()
    {
        self::$taskRouter = new TaskRouterMap();
    }

    /**
     * 返回task路由对象
     * @return TaskRouterMap
     */
    static public function getTaskRouter(): TaskRouterMap
    {
        return self::$taskRouter;
    }

    /**
     * 设置websocket路由对象
     */
    static public function setChannelRouter()
    {
        self::$channelRouter = new ChannelRouterMap();
    }

    /**
     * 返回websocket路由对象
     * @return ChannelRouterMap
     */
    static public function getChannelRouter(): ChannelRouterMap
    {
        return self::$channelRouter;
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
    static public function setMysqlPool(string $configName)
    {
        self::$mysqlPool = new MysqlPool($configName);
    }

    /**
     * 返回mysql连接池
     * @return MysqlPool
     */
    static public function getMysqlPool(): MysqlPool
    {
        return self::$mysqlPool;
    }

    /**
     * mongo连接池
     * @var MongoPool $mongoPool
     */
    static private $mongoPool;

    /**
     * 设置mongo连接池
     * @param string $configName
     * @throws Exception
     */
    static public function setMongoPool(string $configName)
    {
        self::$mongoPool = new MongoPool($configName);
    }

    /**
     * 返回mongo连接池
     * @return MongoPool
     */
    static public function getMongoPool(): MongoPool
    {
        return self::$mongoPool;
    }

    /**
     * redis连接池
     * @var RedisPool $redisPool
     */
    static private $redisPool;

    /**
     * 设置redis连接池
     * @param string $configName
     * @throws Exception
     */
    static public function setRedisPool(string $configName)
    {
        self::$redisPool = new RedisPool($configName);
    }

    /**
     * 返回redis连接池
     * @return RedisPool
     */
    static public function getRedisPool(): RedisPool
    {
        return self::$redisPool;
    }

    /**
     * RabbitMq的连接池
     * @var RabbitMQPool $rabbitPool
     */
    static private $rabbitPool;

    /**
     * 设置RabbitMQ的连接池
     * @param string $configName
     * @throws Exception
     */
    static public function setRabbitMQPool(string $configName = '')
    {
        self::$rabbitPool = new RabbitMQPool($configName);
    }

    /**
     * 返回RabbitMQ的连接池
     * @return RabbitMQPool
     */
    static public function getRabbitMQPool(): RabbitMQPool
    {
        return self::$rabbitPool;
    }

    /**
     * @var Log $log
     */
    static private $log;

    /**
     * 获取日志记录对象
     */
    static public function getLog(): Log
    {
        return self::$log;
    }

    /**
     * 设置记录对象
     */
    static public function setLog()
    {
        self::$log = new Log();
    }

    /**
     * 加载公共文件
     * @param string $projectName
     */
    public static function loadCommonFile(string $projectName = '')
    {
        include_once dirname(__FILE__) . "/Common/functions.php";
        if ($projectName != '') {
            $filePath = dirname(__FILE__) . "/../app/$projectName/Common/functions.php";
            if (file_exists($filePath)) {
                include_once $filePath;
            }
        }
    }
}