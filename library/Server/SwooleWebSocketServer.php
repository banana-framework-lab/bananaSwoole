<?php

namespace Library\Server;

use Library\Base\Server\BaseSwooleServer;
use Library\Config;
use Library\Entity\Swoole\EntitySwooleServer;
use Library\Entity\Swoole\EntitySwooleWebSocketSever;
use Library\Helper\RequestHelper;
use Library\Helper\ResponseHelper;
use Library\Router;
use Library\Virtual\Server\AbstractServer;
use Swoole\Http\Request as SwooleHttpRequest;
use Swoole\Server\Task;
use Swoole\Table;
use Swoole\Timer;
use Swoole\WebSocket\Frame as SwooleSocketFrame;
use Swoole\WebSocket\Server as SwooleSocketServer;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;

/**
 * Class SwooleWebSocketServer
 * @package Library\Server
 */
class SwooleWebSocketServer extends BaseSwooleServer
{

    /**
     * SwooleWebSocketServer constructor.
     */
    public function __construct(AbstractServer $appServer)
    {
        parent::__construct($appServer);

        //初始化SwooleWebSocketSever
        EntitySwooleWebSocketSever::instanceStart();

        //初始化全局对象
        EntitySwooleServer::setInstance(EntitySwooleWebSocketSever::getInstance());

        $this->server = EntitySwooleServer::getInstance();
        $this->port = Config::get('swoole.socket.port');
        $this->workerNum = Config::get('swoole.socket.worker_num');
        $this->taskNum = Config::get('swoole.socket.task_num', ($this->workerNum) * 4);

        // bindTable初始化
        $this->bindTable = new Table($this->workerNum * 2000);
        $this->bindTable->column('channel', Table::TYPE_STRING, 50);
        $this->bindTable->column('handler', Table::TYPE_STRING, 100);
        $this->bindTable->column('http', Table::TYPE_INT);
        $this->bindTable->create();

        // 设置bindTable
        $this->appServer->setBindTable($this->bindTable);

        // reloadTable初始化
        $this->reloadTable = new Table($this->workerNum * 100);
        $this->reloadTable->column('iNode', Table::TYPE_STRING, 50);
        $this->reloadTable->column('mTime', Table::TYPE_STRING, 50);
        $this->reloadTable->create();
    }

    /**
     * 启动WebSocket服务
     */
    public function run()
    {
        $this->server->set([
            'worker_num' => $this->workerNum,
            'task_worker_num' => $this->taskNum,
            'task_enable_coroutine' => true,
            'reload_async' => true,
            'max_wait_time' => 5,
            'log_level' => 5
        ]);
        $this->server->on('WorkerStart', [$this, 'onWorkerStart']);
        $this->server->on('Request', [$this, 'onRequest']);
        $this->server->on('Task', [$this, 'onTask']);
        $this->server->on('Open', [$this, 'onOpen']);
        $this->server->on('Close', [$this, 'onClose']);
        $this->server->on('Message', [$this, 'onMessage']);
        $this->server->on('WorkerStop', [$this, 'onWorkerStop']);
        $this->server->on('WorkerExit', [$this, 'onWorkerExit']);
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
        // 配置文件初始化
        Config::instanceStart();

        if (!$server->taskworker && $workerId <= 0) {
            if (Config::get('app.debug')) {
                $this->reloadTickId = Timer::tick(1000, $this->autoHotReload());
            }
            $this->startEcho();
        }

        $courseName = $server->taskworker ? 'task' : 'worker';

        go(function () use ($server, $workerId, $courseName) {
            if ($this->appServer->start($server, $workerId)) {
                echo "###########" . str_pad("{$courseName}_pid: {$server->worker_pid}    {$courseName}_id: {$workerId}    start success", 53, ' ', STR_PAD_BOTH) . "###########\n";
            } else {
                echo "###########" . str_pad("{$courseName}_pid: {$server->worker_pid}    {$courseName}_id: {$workerId}    start fail", 53, ' ', STR_PAD_BOTH) . "###########\n";
                $server->shutdown();
            }
        });
    }

    /**
     * onTask事件
     * @param SwooleSocketServer $server
     * @param Task $task
     */
    public function onTask(SwooleSocketServer $server, Task $task)
    {
        $this->appServer->task($server, $task);
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

        $allowOrigins = Config::get('app.allow_origin', []);

        if (isset($request->header['origin']) && in_array(strtolower($request->header['origin']), $allowOrigins)) {
            $response->header('Access-Control-Allow-Origin', $request->header['origin']);
            $response->header('Access-Control-Allow-Credentials', 'true');
            $response->header('Access-Control-Allow-Methods', 'GET, POST, DELETE, PUT, PATCH, OPTIONS');
            $response->header('Access-Control-Allow-Headers', 'x-requested-with,User-Platform,Content-Type,X-Token');
        }
        $response->header('Content-type', 'application/json');

        $this->appServer->request($request, $response);
    }

    /**
     * open事件回调
     * @param SwooleSocketServer $server
     * @param SwooleHttpRequest $request
     */
    public function onOpen(SwooleSocketServer $server, SwooleHttpRequest $request)
    {
        $this->appServer->open($server, $request);
    }


    /**
     * 收到消息回调
     * @param SwooleSocketServer $server
     * @param SwooleSocketFrame $frame
     */
    public function onMessage(SwooleSocketServer $server, SwooleSocketFrame $frame)
    {
        $this->appServer->message($server, $frame);
    }

    /**
     * 关闭webSocket时的回调
     * @param SwooleSocketServer $server
     * @param int $fd
     */
    public function onClose(SwooleSocketServer $server, int $fd)
    {
        $this->appServer->close($server, $fd);
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
        if ($server) {
            echo "###########" . str_pad("worker_pid: {$workerPid}  worker_id: {$workerId}  exitCode: {$exitCode}  sign:{$signal}  error stop", 53, ' ', STR_PAD_BOTH) . "###########\n";
        }
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

    /**
     * onWorkerExit事件
     *
     * @param SwooleSocketServer $server
     * @param int $workerId
     */
    public function onWorkerExit(SwooleSocketServer $server, int $workerId)
    {
        Timer::clear($this->reloadTickId);
        $this->appServer->exit($server, $workerId);
        echo "###########" . str_pad("worker_pid: {$server->worker_pid}    worker_id: {$workerId}    Exit", 53, ' ', STR_PAD_BOTH) . "###########\n";
    }
}