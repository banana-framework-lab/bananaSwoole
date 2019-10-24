<?php

namespace Library\Server;

use Library\Config;
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
        Config::instanceStart();
    }

}