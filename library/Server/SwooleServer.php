<?php

namespace Library\Server;

use Library\Config;
use Library\Helper\RequestHelper;
use Library\Helper\ResponseHelper;
use Library\Router;
use Swoole\Http\Server as SwooleHttpServer;
use Swoole\WebSocket\Server as SwooleWebSocketServer;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/10/22
 * Time: 16:35
 */
class SwooleServer
{
    /**
     * @var SwooleHttpServer|SwooleWebSocketServer $server
     */
    protected $server;

    /**
     * @var int $port
     */
    protected $port;

    /**
     * @var int $workerNum
     */
    protected $workerNum;

    /**
     * SwooleServer constructor.
     */
    public function __construct()
    {
        // Config初始化
        Config::instanceSwooleStart();
    }

    /**
     * 回收对象
     * @param int $workerId
     */
    protected function recoverInstance(int $workerId)
    {
        var_dump(RequestHelper::getInstance());
        var_dump(ResponseHelper::getInstance());
        var_dump(Router::getRouteInstance());
        RequestHelper::delInstance($workerId);
        ResponseHelper::delInstance($workerId);
        Router::delRouteInstance($workerId);
    }
}