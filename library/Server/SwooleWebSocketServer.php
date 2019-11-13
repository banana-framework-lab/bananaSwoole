<?php

namespace Library\Server;

use Library\Config;
use Library\Entity\Swoole\EntitySwooleServer;
use Library\Entity\Swoole\EntitySwooleWebSocketSever;
use Library\Helper\RequestHelper;
use Library\Helper\ResponseHelper;
use Library\Router;
use Library\WebSocketServerApp;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Swoole\Http\Request as SwooleHttpRequest;
use Swoole\Table;
use Swoole\Timer;
use Swoole\WebSocket\Frame as SwooleSocketFrame;
use Swoole\WebSocket\Server as SwooleSocketServer;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;
use Throwable;

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

        //初始化SwooleWebSocketSever
        EntitySwooleWebSocketSever::instanceStart();

        //初始化全局对象
        EntitySwooleServer::setInstance(EntitySwooleWebSocketSever::getInstance());

        $this->server = EntitySwooleServer::getInstance();
        $this->port = Config::get('swoole.socket.port');
        $this->workerNum = Config::get('swoole.socket.worker_num');
        $this->workerNum = 1;

        // bindTable初始化
        $this->bindTable = new Table($this->workerNum * 5000);
        $this->bindTable->column('channel', Table::TYPE_STRING, 50);
        $this->bindTable->column('handler', Table::TYPE_STRING, 100);
        $this->bindTable->column('http', Table::TYPE_INT);
        $this->bindTable->create();

        // reloadTable初始化
        $this->reloadTable = new Table($this->workerNum * 5000);
        $this->reloadTable->column('mTime', Table::TYPE_STRING, 50);
        $this->reloadTable->create();
    }

    /**
     * worker启动完成后报的程序信息
     * @param SwooleSocketServer $server
     */
    private function startEcho(SwooleSocketServer $server)
    {
        echo "\n";
        echo "---------------------------------------------------------------------------\n";
        echo '|' . str_pad("webSocketServer start", 73, ' ', STR_PAD_BOTH) . "|\n";
        echo "---------------------------------------------------------------------------\n";
        echo "|                                                                         |\n";
        echo '|' . str_pad("listen_address: 0.0.0.0  listen_port: {$this->port}  time: {$this->startDateTime}", 73, ' ', STR_PAD_BOTH) . "|\n";
        echo "|                                                                         |\n";
        echo '|' . str_pad("manage_pid: {$server->manager_pid}      master_pid: {$server->master_pid}      worker_number: {$this->workerNum}", 73, ' ', STR_PAD_BOTH) . "|\n";
        echo "|                                                                         |\n";
        echo "---------------------------------------------------------------------------\n";
        echo "\n";
    }

    /**
     * worker启动完成后开启自动热加载
     * @param SwooleSocketServer $server
     * @return \Closure
     */
    private function autoHotReload(SwooleSocketServer $server)
    {
        return function () use ($server) {
            $pathList = [
                'app' => dirname(__FILE__) . '/../../app',
                'route' => dirname(__FILE__) . '/../../route',
                'channel' => dirname(__FILE__) . '/../../channel',
                'config' => dirname(__FILE__) . '/../../config',
            ];
            foreach ($pathList as $pathKey => $pathValue) {
                $dirIterator = new RecursiveDirectoryIterator($pathValue);
                $iterator = new RecursiveIteratorIterator($dirIterator);
                /* @var SplFileInfo $fileValue */
                foreach ($iterator as $fileKey => $fileValue) {
                    $ext = $fileValue->getExtension();
                    if ($ext == 'php') {
                        $iNode = $fileValue->getInode();
                        $mTime = $fileValue->getMTime();
                        if ($this->reloadTable->exist($iNode)) {
                            $oldTime = $this->reloadTable->get($iNode)['mTime'];
                            if ($oldTime != $mTime) {
                                $this->reloadTable->set($iNode, ['mTime' => $mTime]);
                                $server->reload();
                                return;
                            }
                        } else {
                            $this->reloadTable->set($iNode, ['mTime' => $mTime]);
                            return;
                        }
                    }
                }
            }
        };
    }

    /**
     * 启动WebSocket服务
     */
    public function run()
    {
        $this->server->set([
            'worker_num' => $this->workerNum,
            'reload_async' => true
        ]);
        $this->server->on('WorkerStart', [$this, 'onWorkerStart']);
        $this->server->on('Request', [$this, 'onRequest']);
        $this->server->on('Open', [$this, 'onOpen']);
        $this->server->on('Message', [$this, 'onMessage']);
        $this->server->on('Close', [$this, 'onClose']);
        $this->server->on('WorkerStop', [$this, 'onWorkerStop']);
        $this->server->on('WorkerError', [$this, 'onWorkerError']);

        $this->startDateTime = date('Y-m-d H:i:s');

        $this->server->start();
    }


    /**
     * onWorkerStart事件
     * @param SwooleSocketServer $server
     * @param int $workerId
     */
    public function onWorkerStart(SwooleSocketServer $server, int $workerId)
    {
        if ($workerId <= 0) {
            $this->startEcho($server);
            Timer::tick(1000, $this->autoHotReload($server));
        }

        $this->appServerList[$server->worker_id] = new WebSocketServerApp($this->bindTable);

        /* @var WebSocketServerApp $app */
        $app = $this->appServerList[$server->worker_id];

        if ($app->init($server, $workerId)) {
            echo "###########" . str_pad("worker_pid: {$server->worker_pid}    worker_id: {$workerId}    start", 53, ' ', STR_PAD_BOTH) . "###########\n";
        }
    }

    /**
     * 处理Http的请求
     *
     * @param SwooleRequest $request
     * @param SwooleResponse $response
     */
    public function onRequest(SwooleRequest $request, SwooleResponse $response)
    {
        defer(function () {
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

        /* @var WebSocketServerApp $app */
        $app = $this->appServerList[EntitySwooleServer::getInstance()->worker_id];
        $app->run($request, $response);
    }

    /**
     * open事件回调
     * @param SwooleSocketServer $server
     * @param SwooleHttpRequest $request
     */
    public function onOpen(SwooleSocketServer $server, SwooleHttpRequest $request)
    {
        /* @var WebSocketServerApp $app */
        $app = $this->appServerList[EntitySwooleServer::getInstance()->worker_id];
        $app->open($server, $request);
    }


    /**
     * 收到消息回调
     * @param SwooleSocketServer $server
     * @param SwooleSocketFrame $frame
     */
    public function onMessage(SwooleSocketServer $server, SwooleSocketFrame $frame)
    {
        /* @var WebSocketServerApp $app */
        $app = $this->appServerList[EntitySwooleServer::getInstance()->worker_id];
        $app->message($server, $frame);
    }

    /**
     * 关闭webSocket时的回调
     * @param SwooleSocketServer $server
     * @param int $fd
     */
    public function onClose(SwooleSocketServer $server, int $fd)
    {
        /* @var WebSocketServerApp $app */
        $app = $this->appServerList[EntitySwooleServer::getInstance()->worker_id];
        $app->close($server, $fd);
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
        echo "###########" . str_pad("worker_pid: {$server->worker_pid}  worker_id: {$workerId}  exitCode: {$exitCode}  sign:{$signal}  error stop", 53, ' ', STR_PAD_BOTH) . "###########\n";
    }

    /**
     * onWorkerStop事件
     *
     * @param SwooleSocketServer $server
     * @param int $workerId
     */
    public function onWorkerStop(SwooleSocketServer $server, int $workerId)
    {
        echo "###########" . str_pad("worker_pid: {$server->worker_pid}    worker_id: {$workerId}    stop", 53, ' ', STR_PAD_BOTH) . "###########\n";
    }
}