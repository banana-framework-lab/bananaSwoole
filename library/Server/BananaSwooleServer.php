<?php

namespace Library\Server;

use Library\Container;
use Library\Server\Functions\AutoReload;
use Library\Server\Functions\WorkStartEcho;
use Library\Virtual\Server\AbstractSwooleServer;
use Swoole\Coroutine;
use Swoole\Server\Task;
use Swoole\Table;
use Swoole\Timer;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\WebSocket\Server as SwooleWebSocketServer;
use Throwable;

/**
 * Class SwooleWebSocketServer
 * @package Library\Server
 */
class BananaSwooleServer
{

    /**
     * @var SwooleWebSocketServer $server
     */
    protected $server;

    /**
     * @var AbstractSwooleServer $appServer
     */
    protected $appServer;

    /**
     * @var int $workerNum
     */
    protected $workerNum;

    /**
     * @var int $taskNum
     */
    protected $taskNum;

    /**
     * @var Table $bindTable
     */
    protected $bindTable;

    /**
     * @var string $serverConfigIndex
     */
    protected $serverConfigIndex;

    /**
     * @var WorkStartEcho $workStartEcho
     */
    protected $workStartEcho;

    /**
     * @var AutoReload $autoReload
     */
    protected $autoReload;

    /**
     * @var int $echoWidth
     */
    protected $echoWidth = 75;

    /**
     * @var int $cliParamNumber
     */
    protected $cliParamNumber;

    /**
     * @var array $cliParamData
     */
    protected $cliParamData;

    /**
     * @var string $serverName
     */
    protected $serverName;


    /**
     * BananaSwooleServer constructor.
     * @param string $serverConfigIndex
     */
    public function __construct(string $serverConfigIndex)
    {
        $this->serverConfigIndex = $serverConfigIndex;
        Container::setServerConfigIndex($this->serverConfigIndex);
        Container::setConfig();
        Container::setRequest();
        Container::setResponse();
        Container::setRouter();

        Container::getConfig()->initSwooleConfig();
        Container::setSwooleSever($this->serverConfigIndex);

        $this->autoReload = new AutoReload();

        global $argc;
        $this->cliParamNumber = $argc;

        global $argv;
        $this->cliParamData = $argv;

        if ($this->cliParamData[0] != 'bananaSwoole') {
            $this->serverName = str_replace('.php', '', $this->cliParamData[0]);
        } else {
            $this->serverName = $this->cliParamData[2];
        }
    }

    /**
     * SwooleWebSocketServer constructor.
     * @param AbstractSwooleServer $appServer
     * @return BananaSwooleServer
     */
    public function setServer(AbstractSwooleServer $appServer): BananaSwooleServer
    {
        $this->appServer = $appServer;
        $this->server = Container::getSwooleServer();
        $this->workerNum = Container::getConfig()->get("swoole.{$this->serverConfigIndex}.worker_num", 4);
        $this->taskNum = Container::getConfig()->get("swoole.{$this->serverConfigIndex}.task_num", ($this->workerNum) * 4);

        // bindTable初始化
        $this->bindTable = new Table($this->workerNum * 2000);
        $this->bindTable->column('channel', Table::TYPE_STRING, 50);
        $this->bindTable->column('handler', Table::TYPE_STRING, 100);
        $this->bindTable->column('http', Table::TYPE_INT);
        $this->bindTable->create();

        // 设置bindTable
        $this->appServer->setBindTable($this->bindTable);

        // reloadTable初始化
        $reloadTable = new Table($this->workerNum * 500);
        $reloadTable->column('iNode', Table::TYPE_STRING, 50);
        $reloadTable->column('mTime', Table::TYPE_STRING, 50);
        $reloadTable->create();
        $this->autoReload->setReloadTable($reloadTable);

        return $this;
    }

    /**
     * 启动WebSocket服务
     */
    public function run()
    {
        if (!$this->appServer) {
            echo "appServer对象不能为空" . PHP_EOL;
            exit;
        }

        $pidFilePath = dirname(__FILE__) . "/../Runtime/Command/{$this->serverName}";
        $this->server->set([
            'worker_num' => $this->workerNum,
            'task_worker_num' => $this->taskNum,
            'dispatch_mode' => 2,
            'task_enable_coroutine' => true,
            'reload_async' => true,
            'max_wait_time' => 5,
            'log_level' => LOG_NOTICE,
            'pid_file' => Container::getConfig()->get("swoole.{$this->serverConfigIndex}.pid_file", $pidFilePath),
            'hook_flags' => SWOOLE_HOOK_ALL,
            'enable_coroutine' => true
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

        $this->server->start();
    }

    /**
     * onWorkerStart事件
     * @param Server $server
     * @param int $workerId
     */
    public function onWorkerStart(Server $server, int $workerId)
    {
        try {
            // 配置文件初始化
            Container::getConfig()->initConfig();

            // Pool默认启动
            $defaultInitList = Container::getConfig()->get('pool.default_init_list', []);
            foreach ($defaultInitList as $initPool) {
                $poolName = ucfirst(strtolower($initPool));
                if (method_exists(Container::class, "set{$poolName}Pool")) {
                    $methodName = "set{$poolName}Pool";
                    Container::$methodName(Container::getConfig()->get('pool.default_config_name', 'default'));
                }
            }

            // 加载library的Common文件
            Container::loadCommonFile();
            // 当且仅当非task进程，id为0时的进程触发热重启
            if (!$server->taskworker && $workerId <= 0) {
                if (Container::getConfig()->get('app.is_auto_reload', false)) {
                    $this->autoReload->setReloadTickId(Timer::tick(1000, function () {
                        $this->autoReload->main($this->server);
                    }));
                }
                $this->workStartEcho = new WorkStartEcho();
                $this->workStartEcho->serverType = 'SwooleServer';
                $this->workStartEcho->port = Container::getConfig()->get("swoole.{$this->serverConfigIndex}.port", 9501);
                $this->workStartEcho->taskNum = Container::getConfig()->get("swoole.{$this->serverConfigIndex}.task_num", ($this->workerNum) * 4);
                $this->workStartEcho->workerNum = Container::getConfig()->get("swoole.{$this->serverConfigIndex}.task_num", ($this->workerNum) * 4);
                $this->workStartEcho->echoWidth = $this->echoWidth;
                $this->workStartEcho->xChar = '#';
                $this->workStartEcho->yChar = '|';
                $this->workStartEcho->main($this->server, $this->autoReload);
            }
        } catch (Throwable $error) {
            $server->stop($workerId);
        }

        $courseName = $server->taskworker ? 'task' : 'worker';
        $msgHead = "{$courseName}_pid: {$server->worker_pid}    {$courseName}_id: {$workerId}";

        go(function () use ($server, $workerId, $msgHead) {
            if ($this->appServer->start($server, $workerId)) {
                echo "###########" . str_pad(
                        "{$msgHead}  start success",
                        $this->echoWidth - 22,
                        ' ',
                        STR_PAD_BOTH
                    ) . "###########" . PHP_EOL;
            } else {
                echo "###########" . str_pad(
                        "{$msgHead}  start fail",
                        $this->echoWidth - 22,
                        ' ',
                        STR_PAD_BOTH
                    ) . "###########" . PHP_EOL;
                $server->shutdown();
            }
        });
    }

    /**
     * onTask事件
     * @param Server $server
     * @param Task $task
     */
    public function onTask(Server $server, Task $task)
    {
        $this->appServer->task($server, $task);
    }

    /**
     * 处理Http的请求
     *
     * @param Request $request
     * @param Response $response
     */
    public function onRequest(Request $request, Response $response)
    {
        try {
            defer(function () {
                $cId = Coroutine::getuid();
                //回收请求数据
                Container::getRequest()->delRequest($this->server->worker_id, $cId);
                //回收返回数据
                Container::getResponse()->delResponse($this->server->worker_id, $cId);
                //回收路由数据
                Container::getRouter()->delRoute($this->server->worker_id, $cId);
            });

            // 适配谷歌浏览器显示favicon.ico
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

            $allowOrigins = Container::getConfig()->get('app.allow_origin', []);

            if (isset($request->header['origin']) && in_array(strtolower($request->header['origin']), $allowOrigins)) {
                $response->header('Access-Control-Allow-Origin', $request->header['origin']);
                $response->header('Access-Control-Allow-Credentials', 'true');
                $response->header('Access-Control-Allow-Methods', 'GET, POST, DELETE, PUT, PATCH, OPTIONS');
                $response->header('Access-Control-Allow-Headers', 'x-requested-with,User-Platform,Content-Type,X-Token');
            }

            $response->header('Content-type', 'application/json');

            //初始化请求实体类
            $cId = Coroutine::getuid();
            Container::getRequest()->setRequest($request, $this->server->worker_id, $cId);
            Container::getResponse()->setResponse($response, $this->server->worker_id, $cId);
            $this->appServer->request($request, $response);
        } catch (Throwable $error) {
            $workerId = Container::getSwooleServer()->worker_id;
            $errorMsg = $error->getMessage();
            echo "###########" . str_pad("worker_id: {$workerId} error", $this->echoWidth - 22, ' ', STR_PAD_BOTH) . "###########" . PHP_EOL;
            echo "$errorMsg" . PHP_EOL;
            $response->status(500);
        }
    }

    /**
     * open事件回调
     * @param Server $server
     * @param Request $request
     */
    public function onOpen(Server $server, Request $request)
    {
        $this->appServer->open($server, $request);
    }


    /**
     * 收到消息回调
     * @param Server $server
     * @param Frame $frame
     */
    public function onMessage(Server $server, Frame $frame)
    {
        $this->appServer->message($server, $frame);
    }

    /**
     * 关闭webSocket时的回调
     * @param Server $server
     * @param int $fd
     */
    public function onClose(Server $server, int $fd)
    {
        $this->appServer->close($server, $fd);
    }

    /**
     * onWorkerError事件
     *
     * @param Server $server
     * @param int $workerId
     * @param int $workerPid
     * @param int $exitCode
     * @param int $signal
     */
    public function onWorkerError(Server $server, int $workerId, int $workerPid, int $exitCode, int $signal)
    {
        if ($server) {
            $courseName = $server->taskworker ? 'task' : 'worker';
            echo "###########" . str_pad("{$courseName}_pid: {$workerPid} {$courseName}_id: {$workerId} exitCode: {$exitCode} sign:{$signal}  error", $this->echoWidth - 22, ' ', STR_PAD_BOTH) . "###########" . PHP_EOL;
        }
    }

    /**
     * onWorkerStop事件
     *
     * @param Server $server
     * @param int $workerId
     */
    public function onWorkerStop(Server $server, int $workerId)
    {
        $courseName = $server->taskworker ? 'task' : 'worker';
        echo "###########" . str_pad("{$courseName}_pid: {$server->worker_pid}    {$courseName}_id: {$workerId}    stop", $this->echoWidth - 22, ' ', STR_PAD_BOTH) . "###########" . PHP_EOL;
    }

    /**
     * onWorkerExit事件
     *
     * @param Server $server
     * @param int $workerId
     */
    public function onWorkerExit(Server $server, int $workerId)
    {
        $this->autoReload->getReloadTickId() && Timer::clear($this->autoReload->getReloadTickId());
        $this->appServer->exit($server, $workerId);
        $courseName = $server->taskworker ? 'task' : 'worker';
        echo "###########" . str_pad("{$courseName}_pid: {$server->worker_pid}    {$courseName}_id: {$workerId}    Exit", $this->echoWidth - 22, ' ', STR_PAD_BOTH) . "###########" . PHP_EOL;
    }
}