<?php
/**
 * WebSocket通讯类
 * User: zzh
 * Date: 2019/08/22
 */

namespace Library\Server;

use App\Server\Event\ServerEvent;
use Library\Entity\Model\Cache\EntityRedis;
use Library\Entity\Model\DataBase\EntityMongo;
use Library\Entity\Model\DataBase\EntityMysql;
use Library\Entity\Swoole\EntitySwooleWebSocketSever;
use Swoole\Http\Request as SwooleHttpRequest;
use Swoole\WebSocket\Frame as SwooleSocketFrame;
use Swoole\WebSocket\Server as SwooleSocketServer;

class SwooleWebSocketServer extends SwooleServer
{

    /**
     * @var SwooleSocketServer
     */
    private $server;

    /**
     * 进程启动时间
     *
     * @var integer
     */
    static public $processStartTime;

    /**
     * SwooleWebSocketServer constructor.
     */
    public function __construct()
    {
        parent::__construct();
        EntitySwooleWebSocketSever::instanceStart();
        $this->server = EntitySwooleWebSocketSever::getInstance();
    }

    /**
     * 启动WebSocket服务
     */
    public function run()
    {
        //初始化数据库全局对象
        EntityMysql::instanceStart();
        EntityMongo::instanceStart();
        EntityRedis::instanceStart();

        //webSocketServer的数据配置
        $this->server->set([
            'worker_num' => 4,
            'daemonize' => false,
            'task_worker_num' => 1,
            'log_file' => RUNNING_LOG,
            'log_level' => 0,
        ]);
        $this->server->on('Open', [$this, 'onOpen']);
        $this->server->on('Message', [$this, 'onMessage']);
        $this->server->on('Task', [$this, 'onTask']);
        $this->server->on('Finish', [$this, 'onFinish']);
        $this->server->on('Close', [$this, 'onClose']);
        $this->server->on('WorkerStart', [$this, 'onWorkerStart']);

        $this->server->start();
    }


    /**
     * onWorkerStart事件
     *
     * @param SwooleSocketServer $server
     * @param int $workId
     */
    public function onWorkerStart(SwooleSocketServer $server, $workId)
    {
        if ($workId < ($server->setting['worker_num'])) {
            $serverEvent = new ServerEvent($server);
            var_dump('onWorkerStart');
            $serverEvent->onWorkerStart($server, $workId);
        }
    }

    /**
     * open事件回调
     *
     * @param SwooleSocketServer $server
     * @param SwooleHttpRequest $req
     */
    public function onOpen(SwooleSocketServer $server, SwooleHttpRequest $req)
    {
        $serverEvent = new ServerEvent($server);
        var_dump('onOpen');
        $serverEvent->onOpen($server, $req);
    }

    /**
     * 收到消息回调
     *
     * @param SwooleSocketServer $server
     * @param SwooleSocketFrame $frame
     */
    public function onMessage(SwooleSocketServer $server, SwooleSocketFrame $frame)
    {
        $serverEvent = new ServerEvent($server);
        var_dump('onMessage');
        $serverEvent->onMessage($server, $frame);
    }

    /**
     * 关闭webSocket时的回调
     *
     * @param SwooleSocketServer $server
     * @param int $fd
     */
    public function onClose(SwooleSocketServer $server, int $fd)
    {
        $serverEvent = new ServerEvent($server);
        var_dump('onClose');
        $serverEvent->onClose($server, $fd);
    }

    /**
     * 执行task时回调
     *
     * @param SwooleSocketServer $server
     * @param $taskId
     * @param $fromId
     * @param $data
     */
    public function onTask(SwooleSocketServer $server, $taskId, $fromId, $data)
    {
        $serverEvent = new ServerEvent($server);
        $serverEvent->onTask($server, $taskId, $fromId, $data);
    }

    /**
     * @param SwooleSocketServer $server
     * @param $taskId
     * @param $data
     */
    public function onFinish(SwooleSocketServer $server, $taskId, $data)
    {

    }

}