<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/12/23
 * Time: 15:40
 */

namespace Library;

use Library\Container\Config;
use Library\Container\Request;
use Library\Container\Response;
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
}