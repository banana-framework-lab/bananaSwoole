<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/10/22
 * Time: 16:35
 */

namespace Library\Base\Server;

use Closure;
use Library\Config;
use Library\App\Server\DefaultSwooleServer;
use Library\Entity\Swoole\EntitySwooleServer;
use Library\Virtual\Server\AbstractSwooleServer;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Swoole\Table;
use Swoole\WebSocket\Server as SwooleWebSocketServer;

/**
 * Class SwooleServer
 * @package Library\Server
 */
class BaseSwooleServer
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
     * @var int $port
     */
    protected $port;

    /**
     * @var int $workerNum
     */
    protected $workerNum;

    /**
     * @var int $taskNum
     */
    protected $taskNum;

    /**
     * @var array $appServerList
     */
    protected $appServerList;

    /**
     * @var Table $bindTable
     */
    protected $bindTable;

    /**
     * @var Table $reloadTable
     */
    protected $reloadTable;

    /**
     * @var int $reloadTickId
     */
    protected $reloadTickId;

    /**
     * @var bool $isFirstStart
     */
    protected $isFirstStart = true;

    /**
     * @var string $startDateTime
     */
    protected $startDateTime;

    /**
     * @var string $serverConfigIndex
     */
    protected $serverConfigIndex = 'index';

    /**
     * 设置配置文件下标
     * @param string $index
     * @return BaseSwooleServer
     */
    public function setConfigIndex(string $index = 'index'): BaseSwooleServer
    {
        $this->serverConfigIndex = $index;
        return $this;
    }

    /**
     * SwooleServer constructor.
     * @param AbstractSwooleServer $appServer
     */
    public function setServerEntity(AbstractSwooleServer $appServer)
    {
        // Config初始化
        Config::instanceSwooleStart();

        // 初始化全局对象
        EntitySwooleServer::instanceStart($this->serverConfigIndex);

        // 非法初始化的类由默认server覆盖
        if (!$appServer) {
            $appServer = new DefaultSwooleServer();
        }

        $this->appServer = $appServer;
    }

    /**
     * worker启动完成后报的程序信息
     * @param string $serverType
     * @param string $xChar
     * @param string $yChar
     * @param int $echoWidth
     */
    protected function startEcho(string $serverType = "SwooleServer", string $xChar = '-', string $yChar = '|', int $echoWidth = 75)
    {
        $logo = helloBananaSwoole(true, 'array');
        $this->startDateTime = date('Y-m-d H:i:s');

        echo "\n";
        foreach ($logo as $key => $value) {
            echo ' ' . str_pad("{$value}", $echoWidth - 2, ' ', STR_PAD_BOTH) . " \n";
        }
        echo "\n";
        echo str_pad("", $echoWidth, $xChar, STR_PAD_BOTH) . "\n";
        echo $yChar . str_pad("$serverType start", $echoWidth - 2, ' ', STR_PAD_BOTH) . "$yChar\n";
        echo str_pad("", $echoWidth, $xChar, STR_PAD_BOTH) . "\n";
        echo $yChar . str_pad("", $echoWidth - 2, ' ', STR_PAD_BOTH) . "$yChar\n";
        echo $yChar . str_pad("listen_ip: 0.0.0.0  listen_port: {$this->port}  address: http://0.0.0.0{$this->port}", $echoWidth - 2, ' ', STR_PAD_BOTH) . "$yChar\n";
        echo $yChar . str_pad("", $echoWidth - 2, ' ', STR_PAD_BOTH) . "$yChar\n";
        echo $yChar . str_pad("manage_pid: {$this->server->manager_pid}      master_pid: {$this->server->master_pid}      worker_number: {$this->workerNum}", $echoWidth - 2, ' ', STR_PAD_BOTH) . "$yChar\n";
        echo $yChar . str_pad("", $echoWidth - 2, ' ', STR_PAD_BOTH) . "$yChar\n";
        echo $yChar . str_pad("autoHotReloadId: {$this->reloadTickId}   task_number: {$this->taskNum}   time: {$this->startDateTime}", $echoWidth - 2, ' ', STR_PAD_BOTH) . "$yChar\n";
        echo $yChar . str_pad("", $echoWidth - 2, ' ', STR_PAD_BOTH) . "$yChar\n";
        echo str_pad("", $echoWidth, $xChar, STR_PAD_BOTH) . "\n";
        echo "\n";
    }


    /**
     * worker启动完成后开启自动热加载
     * @return Closure
     */
    protected function autoHotReload()
    {
        return function () {
            // 读取需要热加载的路径
            $pathList = Config::get('reload.path_list', []);
            $isReload = false;
            $iNodeList = [];

            //判断文件更新或者新增
            foreach ($pathList as $pathKey => $pathValue) {
                $dirIterator = new RecursiveDirectoryIterator($pathValue);
                $iterator = new RecursiveIteratorIterator($dirIterator);

                /* @var SplFileInfo $fileValue */
                foreach ($iterator as $fileKey => $fileValue) {
                    $ext = $fileValue->getExtension();
                    if ($ext == 'php') {
                        $iNode = $fileValue->getInode();
                        $mTime = $fileValue->getMTime();
                        $iNodeList[] = $iNode;
                        if ($this->reloadTable->exist($iNode)) {
                            if ($this->reloadTable->get($iNode)['mTime'] != $mTime) {
                                $this->reloadTable->set($iNode, [
                                    'mTime' => $mTime
                                ]);
                                $isReload = true;
                            }
                        } else {
                            $this->reloadTable->set($iNode, [
                                'mTime' => $mTime
                            ]);
                            $isReload = true;
                        }
                    }
                }
            }

            //判断文件删除
            foreach ($this->reloadTable as $reloadKey => $reloadValue) {
                if (!in_array((int)$reloadKey, $iNodeList)) {
                    $this->reloadTable->del($reloadKey);
                    $isReload = true;
                }
            }

            if (!$this->isFirstStart) {
                if ($isReload) {
                    $this->server->reload();
                }
            } else {
                $this->isFirstStart = false;
            }
        };
    }
}