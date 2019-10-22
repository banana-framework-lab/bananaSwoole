<?php

namespace Library\Server;

use Library\Config;
use Library\WebServerApp;
use Library\Entity\Swoole\EntitySwooleWebSever;
use Swoole\Http\Server as SwooleHttpServer;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;

class SwooleWebServer extends SwooleServer
{
    /**
     * SwooleWebServer constructor.
     */
    public function __construct()
    {
        parent::__construct();
        EntitySwooleWebSever::instanceStart();
        $this->server = EntitySwooleWebSever::getInstance();
        $this->port = Config::get('swoole.web.port');
    }

    /**
     * @var SwooleHttpServer $server
     */
    private $server;

    /**
     * @var int $port
     */
    private $port;

    /**
     * 启动Swoole的http服务
     */
    public function run()
    {
        $this->server->set([
            'worker_num' => 4,
        ]);
        $this->server->on('Request', [$this, 'onRequest']);
        $this->server->on('WorkerStart', [$this, 'onWorkerStart']);
        $this->server->start();

        echo "\n";
        echo "************************************************************************\n";
        echo "*  httpServer服务启动于：http://0.0.0.0:{$this->port} 时间:" . date('Y-m-d H:i:s') . "  *\n";
        echo "************************************************************************\n";
    }

    /**
     * onWorkerStart事件
     *
     * @param SwooleHttpServer $server
     * @param int $workId
     */
    public function onWorkerStart(SwooleHttpServer $server, int $workId)
    {
        //加载App类
        WebServerApp::init();
    }

    /**
     * 处理Http的请求
     *
     * @param SwooleRequest $request
     * @param SwooleResponse $response
     */
    public function onRequest(SwooleRequest $request, SwooleResponse $response)
    {
        // 屏蔽 favicon.ico
        $uri = $request->server['request_uri'];
        if ($uri == '/favicon.ico') {
            $response->status(404);
            $response->end();
        }

        // 支持跨域访问
        $response->header('Access-Control-Allow-Origin', 'https://www.ysdwat.com');
        $response->header('Access-Control-Allow-Credentials', 'true');
        $response->header('Access-Control-Allow-Methods', 'GET, POST, DELETE, PUT, PATCH, OPTIONS');
        $response->header('Access-Control-Allow-Headers', 'x-requested-with,User-Platform,Content-Type,X-Token');
        $response->header('Content-type', 'application/json');
        if ($request->server['request_method'] == 'OPTIONS') {
            $response->status(200);
            $response->end();
            return;
        };

        $response->end(WebServerApp::run($request));
    }
}