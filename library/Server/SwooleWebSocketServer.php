<?php

namespace Library\Server;

use Library\Config;
use Library\Entity\Swoole\EntitySwooleWebSocketSever;
use Library\WebSocketServerApp;
use Swoole\Http\Request as SwooleHttpRequest;
use Swoole\WebSocket\Frame as SwooleSocketFrame;
use Swoole\WebSocket\Server as SwooleSocketServer;

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
        $this->server->on('Open', [$this, 'onOpen']);
        $this->server->on('Message', [$this, 'onMessage']);
        $this->server->on('Close', [$this, 'onClose']);

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
}