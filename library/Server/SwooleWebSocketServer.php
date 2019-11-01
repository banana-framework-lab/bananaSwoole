<?php

namespace Library\Server;

use Library\Binder;
use Library\Config;
use Library\Entity\Swoole\EntitySwooleWebSocketSever;
use Library\Helper\RequestHelper;
use Library\Helper\ResponseHelper;
use Library\Router;
use Library\WebSocketServerApp;
use Swoole\Http\Request as SwooleHttpRequest;
use Swoole\WebSocket\Frame as SwooleSocketFrame;
use Swoole\WebSocket\Server as SwooleSocketServer;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;

/**
 * Class SwooleWebSocketServer
 * @package Library\Server
 */
class SwooleWebSocketServer extends SwooleServer
{
    /**
     * SwooleWebSocketServer constructor.
     */
    public function __construct()
    {
        parent::__construct();

        EntitySwooleWebSocketSever::instanceStart();
        Binder::instanceStart();

        $this->server = EntitySwooleWebSocketSever::getInstance();
        $this->port = Config::get('swoole.socket.port');
    }

    /**
     * 启动WebSocket服务
     */
    public function run()
    {
        echo "\n";
        echo "---------------------------------------------------------------------------\n";
        echo "|  webSocketServer服务启动于：ws://0.0.0.0:{$this->port} 时间:" . date('Y-m-d H:i:s') . "  |\n";
        echo "---------------------------------------------------------------------------\n";

        $this->server->set([
//            'worker_num' => $this->workerNum,
            'worker_num' => 1,
            'reload_async' => true
        ]);
        $this->server->on('WorkerStart', [$this, 'onWorkerStart']);
        $this->server->on('Request', [$this, 'onRequest']);
        $this->server->on('Open', [$this, 'onOpen']);
        $this->server->on('Message', [$this, 'onMessage']);
        $this->server->on('Close', [$this, 'onClose']);
        $this->server->on('WorkerStop', [$this, 'onWorkerStop']);
        $this->server->on('WorkerError', [$this, 'onWorkerError']);

        $this->server->start();
    }


    /**
     * onWorkerStart事件
     * @param SwooleSocketServer $server
     * @param int $workerId
     */
    public function onWorkerStart(SwooleSocketServer $server, int $workerId)
    {
        WebSocketServerApp::init($workerId);

        echo "master_pid:{$server->master_pid}  worker_pid:{$server->worker_pid}  worker_id:{$workerId}  启动\n";
    }

    /**
     * 处理Http的请求
     *
     * @param SwooleRequest $request
     * @param SwooleResponse $response
     */
    public function onRequest(SwooleRequest $request, SwooleResponse $response)
    {
        defer(function () use ($response) {
            //回收请求数据
            RequestHelper::delInstance();

            //回收返回数据
            ResponseHelper::delInstance();

            //回收路由数据
            Router::delRouteInstance();
        });

        // 屏蔽 favicon.ico
        if ($request->server['request_uri'] == '/favicon.ico') {
            if (file_exists(dirname(__FILE__) . "/../../public/favicon.ico")) {
                $response->status(200);
                $response->header('Content-Type', 'image/x-icon');
                $response->sendfile(dirname(__FILE__) . "/../../public/favicon.ico");
            } else {
                $response->status(404);
                $response->end();
            }
            return;
        }

        if ($request->server['request_method'] == 'OPTIONS') {
            $response->status(200);
            $response->end();
            return;
        };

        $response->header('Access-Control-Allow-Origin', implode(',', Config::get('app.allow_origin', ['*'])));
        $response->header('Access-Control-Allow-Credentials', 'true');
        $response->header('Access-Control-Allow-Methods', 'GET, POST, DELETE, PUT, PATCH, OPTIONS');
        $response->header('Access-Control-Allow-Headers', 'x-requested-with,User-Platform,Content-Type,X-Token');
        $response->header('Content-type', 'application/json');

        WebSocketServerApp::run($request, $response);
    }

    /**
     * open事件回调
     * @param SwooleSocketServer $server
     * @param SwooleHttpRequest $request
     */
    public function onOpen(SwooleSocketServer $server, SwooleHttpRequest $request)
    {
        WebSocketServerApp::open($server, $request);
    }


    /**
     * 收到消息回调
     * @param SwooleSocketServer $server
     * @param SwooleSocketFrame $frame
     */
    public function onMessage(SwooleSocketServer $server, SwooleSocketFrame $frame)
    {
        WebSocketServerApp::message($server, $frame);
    }

    /**
     * 关闭webSocket时的回调
     * @param SwooleSocketServer $server
     * @param int $fd
     */
    public function onClose(SwooleSocketServer $server, int $fd)
    {
        WebSocketServerApp::close($server, $fd);
    }

    /**
     * onWorkerError事件
     *
     * @param SwooleSocketServer $server
     * @param int $workerId
     * @param int $workerPid
     * @param int $exitCode
     * @param int $signal
     */
    public function onWorkerError(SwooleSocketServer $server, int $workerId, int $workerPid, int $exitCode, int $signal)
    {
        echo "master_id:{$server->master_pid}  worker_pid:{$workerPid}  worker_id:{$workerId}  异常关闭:错误码 {$exitCode},信号 {$signal}\n";
    }

    /**
     * onWorkerStop事件
     *
     * @param SwooleSocketServer $server
     * @param int $workerId
     */
    public function onWorkerStop(SwooleSocketServer $server, int $workerId)
    {
        echo "master_id:{$server->master_pid}  worker_pid:{$server->worker_pid}  worker_id:{$workerId}  正常关闭\n";
    }
}