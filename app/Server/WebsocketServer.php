<?php
/**
 * Websocket通讯类
 * User: zzh
 * Date: 2019/08/22
 */

namespace App\Server;

use App\Library\Entity\Model\DataBase\Mysql;
use App\Server\Event\ServerEvent;
use Illuminate\Database\Capsule\Manager;
use Swoole\Http\Request;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;

class WebsocketServer
{

    /**
     * @var Server
     */
    private $server;

    /**
     * websocket监听的端口号
     */
    const PORT = 9502;

    /**
     * 进程启动时间
     *
     * @var integer
     */
    static public $processStartTime;


    /**
     * 启动websocket服务
     */
    public function run()
    {
        $capsule = new Manager;

        $databaseConfig = IS_SERVER ? DB_LIST['server'] : DB_LIST['local'];
        $capsule->addConnection($databaseConfig);

        // 使得数据库对象全局可用
        $capsule->setAsGlobal();

        //初始化mysql全局对象
        Mysql::setDBInstance($capsule);

        //server的数据配置
        $setting = [
            'worker_num' => 4,
            'daemonize' => false,
            'task_worker_num' => 1,
            'log_file' => RUNNING_LOG,
            'log_level' => 0,
        ];
        // debug模式下不使用ssl
//        if (IS_SERVER) {
            $this->server = new Server("0.0.0.0", self::PORT);
//        } else {
//            $this->server = new Server("0.0.0.0", self::PORT, SWOOLE_PROCESS, SWOOLE_SOCK_TCP | SWOOLE_SSL);
//            $setting['ssl_cert_file'] = SERVER_SSL_PEM;
//            $setting['ssl_key_file'] = SERVER_SSL_KEY;
//            $setting['log_level'] = 5;
//        }


        $this->server->set($setting);

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
     * @param Server $server
     * @param int $workId
     */
    public function onWorkerStart(Server $server, $workId)
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
     * @param Server $server
     * @param Request $req
     */
    public function onOpen(Server $server, Request $req)
    {
        $serverEvent = new ServerEvent($server);
        var_dump('onOpen');
        $serverEvent->onOpen($server, $req);
    }

    /**
     * 收到消息回调
     *
     * @param Server $server
     * @param Frame $frame
     */
    public function onMessage(Server $server, Frame $frame)
    {
        $serverEvent = new ServerEvent($server);
        var_dump('onMessage');
        $serverEvent->onMessage($server, $frame);
    }

    /**
     * 关闭websocket时的回调
     *
     * @param Server $server
     * @param int $fd
     */
    public function onClose($server, $fd)
    {
        $serverEvent = new ServerEvent($server);
        var_dump('onClose');
        $serverEvent->onClose($server, $fd);
    }

    /**
     * 执行task时回调
     *
     * @param Server $server
     * @param $taskId
     * @param $fromId
     * @param $data
     */
    public function onTask(Server $server, $taskId, $fromId, $data)
    {
        $serverEvent = new ServerEvent($server);
        $serverEvent->onTask($server, $taskId, $fromId, $data);
    }

    /**
     * @param Server $server
     * @param $taskId
     * @param $data
     */
    public function onFinish(Server $server, $taskId, $data)
    {
        //$serverEvent = new ServerEvent($server);
        //$serverEvent->onFinish($server, $taskId, $data);
    }

}