<?php

namespace Library\Server;

use Library\Abstracts\Controller\AbstractController;
use Library\Abstracts\Form\AbstractForm;
use Library\Abstracts\Handler\AbstractHandler;
use Library\Abstracts\Server\AbstractSwooleServer;
use Library\Container;
use Library\Container\Channel;
use Library\Container\Instance\ChannelMap;
use Library\Exception\LogicException;
use Library\Server\Functions\AutoReload;
use Library\Server\Functions\WorkStartEcho;
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
 * @package Library\Index
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
     * BananaSwooleServer constructor.
     * @param string $serverConfigIndex
     */
    public function __construct()
    {
        // 获取server的名字
        $bt = debug_backtrace();
        $caller = array_shift($bt);
        $fileName = explode('/', $caller['file']);
        $serverConfigIndex = str_replace('.php', '', array_pop($fileName));

        $this->serverConfigIndex = $serverConfigIndex;
        Container::setServerConfigIndex($this->serverConfigIndex);
        Container::setConfig();
        Container::setRequest();
        Container::setResponse();
        Container::setRouter();

        Container::getConfig()->initSwooleConfig();
        Container::setSever($this->serverConfigIndex);

        $this->autoReload = new AutoReload();
    }

    /**
     * SwooleWebSocketServer constructor.
     * @param AbstractSwooleServer $appServer
     * @return BananaSwooleServer
     */
    public function setServer(AbstractSwooleServer $appServer): BananaSwooleServer
    {
        $this->appServer = $appServer;
        $this->server = Container::getServer()->getSwooleServer();
        $this->workerNum = Container::getConfig()->get("swoole.$this->serverConfigIndex.worker_num", 1);
        $this->taskNum = Container::getConfig()->get("swoole.$this->serverConfigIndex.task_num", ($this->workerNum) * 4);

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

        $pidFilePath = dirname(__FILE__) . "/../../runtime/Server/";

        if (!file_exists($pidFilePath)) {
            mkdir($pidFilePath, 755, true);
        }

        // 记录master进程的id
        $pidFilePath .= ".$this->serverConfigIndex";
        $pidFile = fopen($pidFilePath, "w");
        fwrite($pidFile, posix_getpid());
        fclose($pidFile);

        $this->server->set([
            'worker_num' => $this->workerNum,
            'task_worker_num' => $this->taskNum,
            'dispatch_mode' => 2,
            'task_enable_coroutine' => true,
            'reload_async' => true,
            'max_wait_time' => 5,
            'log_level' => LOG_NOTICE,
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

            // 加载library的Common文件
            Container::loadCommonFile();

            if (!$server->taskworker && $workerId <= 0) {
                $this->workStartEcho = new WorkStartEcho();
                $this->workStartEcho->serverType = $this->serverConfigIndex;
                $this->workStartEcho->port = Container::getConfig()->get("swoole.$this->serverConfigIndex.port", 9501);
                $this->workStartEcho->taskNum = $this->taskNum;
                $this->workStartEcho->workerNum = $this->workerNum;
                $this->workStartEcho->echoWidth = $this->echoWidth;
                $this->workStartEcho->xChar = '#';
                $this->workStartEcho->yChar = '|';
                $this->workStartEcho->main($this->server, $this->autoReload);

                // 当且仅当非task进程，id为0时的进程触发热重启
                if (Container::getConfig()->get('app.is_auto_reload', false)) {
                    $this->autoReload->reloadTickId = Timer::tick(1000, function () {
                        $this->autoReload->main($this->server);
                    });
                }
            }

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

            // Pool默认启动
            $defaultInitList = ['mysql', 'redis', 'rabbit', 'mongo'];
            foreach ($defaultInitList as $initPool) {
                $default_name = Container::getConfig()->get("pool.{$initPool}.index", 'default');
                if (Container::getConfig()->get("$initPool.$default_name")) {
                    $poolName = ucfirst(strtolower($initPool));
                    if (method_exists(Container::class, "set{$poolName}Pool")) {
                        $methodName = "set{$poolName}Pool";
                        Container::$methodName($default_name);
                    }
                }
            }

            $this->appServer->onStart($server, $workerId);
        } catch (Throwable $error) {
            echo $error->getMessage() . PHP_EOL;
            echo $error->getTraceAsString() . PHP_EOL;
            $server->stop($workerId);
            $server->shutdown();
        }

        $courseName = $server->taskworker ? 'task' : 'worker';
        $msgHead = "{$courseName}_pid: $server->worker_pid    {$courseName}_id: $workerId";

        go(function () use ($server, $workerId, $msgHead) {
            if ($this->appServer->onStart($server, $workerId)) {
                echo "###########" . str_pad(
                        "$msgHead  start success",
                        $this->echoWidth - 22,
                        ' ',
                        STR_PAD_BOTH
                    ) . "###########" . PHP_EOL;
            } else {
                echo "###########" . str_pad(
                        "$msgHead  start fail",
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
        try {
            $msgHead = "task_pid: $server->worker_pid    task_id: $server->worker_id";

            // 初始化请求数据
            $taskData = $task->data;

            $routeObject = Container::getTaskRouter()->taskRouter($taskData['task_uri'] ?? '');

            // 初始化方法
            $methodName = $routeObject->getMethod();
            $taskClass = $routeObject->getTask();

            // 初始化控制器
            try {
                if (class_exists($taskClass)) {
                    /* @var AbstractController $controller */
                    $task = new $taskClass($taskData);
                    if (method_exists($task, $methodName)) {
                        $returnData = $task->$methodName();
                        if (!empty($returnData)) {
                            $server->finish($returnData);
                        }
                    } else {
                        echo "###########" . str_pad(
                                "$msgHead  找不到task类的方法 uri:{$taskData['task_uri']}",
                                $this->echoWidth - 22,
                                ' ',
                                STR_PAD_BOTH
                            ) . "###########" . PHP_EOL;
                        return;
                    }
                } else {
                    echo "###########" . str_pad(
                            "$msgHead  找不到task类 uri:{$taskData['task_uri']}",
                            $this->echoWidth - 22,
                            ' ',
                            STR_PAD_BOTH
                        ) . "###########" . PHP_EOL;
                    return;
                }
            } catch (Throwable $e) {
                echo "###########" . str_pad(
                        "$msgHead  task任务出错 uri:{$taskData['task_uri']}",
                        $this->echoWidth - 22,
                        ' ',
                        STR_PAD_BOTH
                    ) . "###########" . PHP_EOL;
                Container::getLog()->error(
                    "task任务出错 uri:{$taskData['task_uri']}", [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                return;
            }
        } catch (Throwable $error) {
            echo "###########" . str_pad(
                    "$msgHead  task任务出错 uri:{$taskData['task_uri']}",
                    $this->echoWidth - 22,
                    ' ',
                    STR_PAD_BOTH
                ) . "###########" . PHP_EOL;
            Container::getLog()->error(
                "task任务出错 uri:{$taskData['task_uri']}", [
                'message' => $error->getMessage(),
                'trace' => $error->getTraceAsString()
            ]);
            return;
        }
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

            if ($request->server['request_method'] == 'OPTIONS') {
                $response->status(200);
                $response->end();
                return;
            }

            //初始化请求实体类
            $cId = Coroutine::getuid();
            Container::getRequest()->setRequest($request, $this->server->worker_id, $cId);
            Container::getResponse()->setResponse($response, $this->server->worker_id, $cId);

            // 标识此次fd为http请求;
            $this->bindTable->set($request->fd, ['http' => 1]);

            $routeObject = Container::getRouter()->controllerRouter($request->server['request_uri']);

            // 初始化方法
            $methodName = $routeObject->getMethod();
            $controllerClass = $routeObject->getController();

            // 初始化请求数据
            $getData = $request->get ?: [];
            $postData = $request->post ?: [];
            $rawContentData = json_decode($request->rawContent(), true) ?: [];
            $requestData = array_merge($getData, $postData, $rawContentData);

            // 初始化请求中间件
            try {
                $formClass = str_replace("Controller", "Form", $controllerClass);
                /* @var AbstractForm $form */
                if (method_exists($formClass, $methodName)) {
                    $form = new $formClass($requestData);
                    $form->$methodName();
                    $requestData = $form->getFormData();
                }
            } catch (LogicException $webE) {

                $responseData = $this->appServer->getExceptionResponse($webE);
                if (!empty($response)) {
                    $response->status(200);
                    if (is_array($responseData)) {
                        $response->header('Content-type', 'application/json;charset=UTF-8');
                        $response->end(json_encode($responseData, JSON_UNESCAPED_UNICODE));
                    } else {
                        $response->end($responseData);
                    }
                } elseif (Container::getConfig()->get('app.debug', false)) {
                    $response->status(200);
                    $response->header('Content-type', 'text/plain;charset=UTF-8');
                    $response->end("{$webE->getMessage()}{$webE->getTraceAsString()}");
                } else {
                    $response->status(500);
                    $response->end();
                }
                return;
            } catch (Throwable $e) {
                if (Container::getConfig()->get('app.debug', false)) {
                    $response->status(200);
                    $response->end("{$e->getMessage()}<br>{$e->getTraceAsString()}");
                } else {
                    $response->status(500);
                    $response->end();
                }
                return;
            }

            // 初始化控制器
            try {
                if (class_exists($controllerClass)) {
                    /* @var AbstractController $controller */
                    $controller = new $controllerClass($requestData);
                    if (method_exists($controller, $methodName)) {
                        $returnData = $controller->$methodName();
                        if (!empty($returnData)) {
                            $response->status(200);
                            if (is_array($returnData)) {
                                $response->header('Content-type', 'application/json;charset=UTF-8');
                                $response->end(json_encode($returnData, JSON_UNESCAPED_UNICODE));
                            } else {
                                $response->end($returnData);
                            }
                        }
                    } else {
                        if (Container::getConfig()->get('app.debug', false)) {
                            $response->status(200);
                            $response->header('Content-type', 'text/plain;charset=UTF-8');
                            $response->end("403找不到{$request->server['request_uri']}");
                        } else {
                            $response->status(403);
                            $response->end();
                        }
                        return;
                    }
                } else {
                    if (Container::getConfig()->get('app.debug', false)) {
                        $response->status(200);
                        $response->header('Content-type', 'text/plain;charset=UTF-8');
                        $response->end("404找不到{$request->server['request_uri']}");
                    } else {
                        $response->status(404);
                        $response->end();
                    }
                    return;
                }
            } catch (Throwable $e) {
                if (Container::getConfig()->get('app.debug', false)) {
                    $response->status(200);
                    if ($e->getCode() != C_EXIT_CODE) {
                        $response->header('Content-type', 'text/plain;charset=UTF-8');
                        $response->end($e->getMessage() . "<br>" . $e->getTraceAsString());
                    } else {
                        $workerId = Container::getServer()->getSwooleServer()->worker_id;
                        $cId = Coroutine::getCid();
                        $response->header('Content-type', 'text/plain;charset=UTF-8');
                        $response->end(Container::getResponse()->dumpFlush($workerId, $cId));
                    }
                } else {
                    $response->status(500);
                    $response->end();
                }
                return;
            }
        } catch (Throwable $error) {
            $workerId = Container::getServer()->getSwooleServer()->worker_id;
            $errorMsg = $error->getMessage();
            echo "###########" . str_pad("worker_id: $workerId error", $this->echoWidth - 22, ' ', STR_PAD_BOTH) . "###########" . PHP_EOL;
            echo "$errorMsg" . PHP_EOL;
            $response->status(500);
            $response->end();
            return;
        }
    }

    /**
     * open事件回调
     * @param Server $server
     * @param Request $request
     */
    public function onOpen(Server $server, Request $request)
    {
        // 初始化请求数据
        $getData = $request->get ?: [];
        $postData = $request->post ?: [];
        $rawContentData = json_decode($request->rawContent(), true) ?: [];
        $openData = array_merge($getData, $postData, $rawContentData);

        // 选出所需通道
        $channelObject = ChannelMap::route($openData);

        // 过滤错误的连接
        if (!$channelObject->getChannel()) {
            $server->disconnect(
                $request->fd,
                Container::getConfig()->get('response.code.no_channel', 404),
                "找不到fd对应的Channel"
            );
            return;
        }

        // open实体方法
        try {
            $handlerClass = $channelObject->getHandler();
            // 初始化Handler
            if (class_exists($handlerClass)) {
                /* @var AbstractHandler $handler */
                $handler = new $handlerClass();
                if (method_exists($handlerClass, 'open')) {
                    // fd绑定通道
                    $this->bindTable->set($request->fd, $channelObject->toArray());
                    // fd打开事件
                    $handler->open($server, $request);
                } else {
                    $server->disconnect(
                        $request->fd,
                        Container::getConfig()->get('response.code.no_channel', 403),
                        Container::getConfig()->get('app.debug', false) ? "找不到open方法" : '已断开连接！'
                    );
                }
            } else {
                $server->disconnect(
                    $request->fd,
                    Container::getConfig()->get('response.code.no_channel', 404),
                    Container::getConfig()->get('app.debug', false) ? "找不到$handlerClass" : '已断开连接.'
                );
            }
        } catch (Throwable $e) {
            echo $e->getMessage() . PHP_EOL . $e->getTraceAsString();
            $server->disconnect(
                $request->fd,
                Container::getConfig()->get('response.code.fatal_error', 500),
                "已断开连接."
            );
        }
    }


    /**
     * 收到消息回调
     * @param Server $server
     * @param Frame $frame
     */
    public function onMessage(Server $server, Frame $frame)
    {
        $tableData = $this->bindTable->get($frame->fd);
        try {
            // 获取所需通道
            $channelObject = new Channel();
            $channelObject->setChannel($tableData['channel'] ?? '');
            $channelObject->setHandler($tableData['handler'] ?? '');

            if (!$channelObject->getChannel()) {
                $server->disconnect(
                    $frame->fd,
                    Container::getConfig()->get('response.code.no_channel', 404),
                    Container::getConfig()->get('app.debug', false) ? "找不到fd对应的Channel" : '已断开连接'
                );
                return;
            }

            // 初始化Handler
            $handlerClass = $channelObject->getHandler();

            // 初始化事件器
            if (class_exists($handlerClass)) {
                /* @var AbstractHandler $handler */
                $handler = new $handlerClass();
                if (method_exists($handlerClass, 'message')) {
                    $handler->message($server, $frame);
                } else {
                    $server->disconnect(
                        $frame->fd,
                        Container::getConfig()->get('response.code.no_message_function', 403),
                        Container::getConfig()->get('app.debug', true) ? "找不到message方法" : "已断开连接"
                    );
                }
            } else {
                $server->disconnect(
                    $frame->fd,
                    Container::getConfig()->get('response.code.no_handler_class', 404),
                    Container::getConfig()->get('app.debug', true) ? "找不到$handlerClass" : "已断开连接!"
                );
            }
        } catch (Throwable $e) {
            echo $e->getMessage() . PHP_EOL . $e->getTraceAsString();
            $server->disconnect(
                $frame->fd,
                Container::getConfig()->get('response.code.fatal_error', 500),
                "已断开连接."
            );
        }
    }

    /**
     * 关闭webSocket时的回调
     * @param Server $server
     * @param int $fd
     */
    public function onClose(Server $server, int $fd)
    {
        $tableData = $this->bindTable->get($fd) ?: [];
        if (!isset($tableData['http'])) {
            $this->bindTable->del($fd);
            return;
        }
        if ($tableData['http'] == 1) {
            $this->bindTable->del($fd);
        } else {
            try {
                // 获取所需通道
                $channelObject = new Channel();
                $channelObject->setChannel($tableData['channel']);
                $channelObject->setHandler($tableData['handler']);
                if (!$channelObject->getChannel()) {
                    echo "###########" . str_pad(
                            "{$fd}找不到fd对应的Channel!",
                            $this->echoWidth - 22,
                            ' ',
                            STR_PAD_BOTH
                        ) . "###########" . PHP_EOL;
                    return;
                }
                // 初始化Handler
                $handlerClass = $channelObject->getHandler();

                // fd解绑Channel
                $this->bindTable->del($fd);

                // 初始化事件器
                if (class_exists($handlerClass)) {
                    /* @var AbstractHandler $handler */
                    $handler = new $handlerClass();
                    if (method_exists($handlerClass, 'open')) {
                        $handler->close($server, $fd);
                    } else {
                        echo "###########" . str_pad(
                                "{$fd}找不到fd对应的close方法!",
                                $this->echoWidth - 22,
                                ' ',
                                STR_PAD_BOTH
                            ) . "###########" . PHP_EOL;
                    }
                } else {
                    echo "###########" . str_pad(
                            "{$fd}找不到fd对应的$handlerClass!",
                            $this->echoWidth - 22,
                            ' ',
                            STR_PAD_BOTH
                        ) . "###########" . PHP_EOL;
                }
            } catch (Throwable $e) {
                echo "###########" . str_pad(
                        "{$fd}找不到fd对应的{$e->getMessage()}!",
                        $this->echoWidth - 22,
                        ' ',
                        STR_PAD_BOTH
                    ) . "###########" . PHP_EOL;
            }
        }
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
        $courseName = $server->taskworker ? 'task' : 'worker';
        echo "###########" . str_pad("{$courseName}_pid: $workerPid {$courseName}_id: $workerId exitCode: $exitCode sign:$signal  error", $this->echoWidth - 22, ' ', STR_PAD_BOTH) . "###########" . PHP_EOL;
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
        echo "###########" . str_pad($courseName . "_pid: " . $server->worker_pid . "    " . $courseName . "_id: " . $workerId . "    stop", $this->echoWidth - 22, ' ', STR_PAD_BOTH) . "###########" . PHP_EOL;
    }

    /**
     * onWorkerExit事件
     *
     * @param Server $server
     * @param int $workerId
     */
    public function onWorkerExit(Server $server, int $workerId)
    {
        $this->autoReload->reloadTickId && Timer::clear($this->autoReload->reloadTickId);
        $this->appServer->exit($server, $workerId);
        $courseName = $server->taskworker ? 'task' : 'worker';
        echo "###########" . str_pad($courseName . "_pid: " . $server->worker_pid . "    " . $courseName . "_id: " . $workerId . "    Exit", $this->echoWidth - 22, ' ', STR_PAD_BOTH) . "###########" . PHP_EOL;
    }
}