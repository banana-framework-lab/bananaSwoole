<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/11/22
 * Time: 20:25
 */

namespace Library\Virtual\Server;

use Swoole\Server\Task;
use Swoole\Table;
use Swoole\Http\Request as SwooleHttpRequest;
use Swoole\WebSocket\Server as SwooleSocketServer;
use Swoole\WebSocket\Frame as SwooleSocketFrame;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;

abstract class AbstractSwooleServer
{
    /**
     * 绑定关系的内存表
     * @var Table $bindTable
     */
    public $bindTable;

    /**
     * 设定绑定表
     * @param Table $table
     */
    public function setBindTable(Table $table)
    {
        $this->bindTable = $table;
    }

    /**
     * onWorkerStart
     * @param SwooleSocketServer $server
     * @param int $workerId
     * @return bool
     */
    abstract public function start(SwooleSocketServer $server, int $workerId): bool;

    /**
     * onOpen
     * @param SwooleSocketServer $server
     * @param SwooleRequest $request
     */
    abstract public function open(SwooleSocketServer $server, SwooleHttpRequest $request);

    /**
     * onMessage
     * @param SwooleSocketServer $server
     * @param SwooleSocketFrame $frame
     */
    abstract public function message(SwooleSocketServer $server, SwooleSocketFrame $frame);

    /**
     * onClose
     * @param SwooleSocketServer $server
     * @param int $fd
     */
    abstract public function close(SwooleSocketServer $server, int $fd);

    /**
     * onRequest
     * @param SwooleRequest $request
     * @param SwooleResponse $response
     */
    abstract public function request(SwooleRequest $request, SwooleResponse $response);

    /**
     * onRequest
     * @param SwooleSocketServer $server
     * @param Task $task
     * @return mixed
     */
    abstract public function task(SwooleSocketServer $server, Task $task);

    /**
     * onWorkerExit
     * @param SwooleSocketServer $server
     * @param int $workerId
     */
    abstract public function exit(SwooleSocketServer $server, int $workerId);

    /**
     * 使用session
     * @param SwooleRequest $request
     * @param SwooleResponse $response
     * @param int $sessionLive
     */
    public function openSession(SwooleRequest &$request, SwooleResponse &$response, int $sessionLive = 86400)
    {
        if (!isset($request->cookie['PHPSESSID']) && isset($request->header['origin'])) {
            $phpSessionId = md5(time() + rand(0, 99999));
            $request->cookie['PHPSESSID'] = $phpSessionId;
            $response->cookie(
                'PHPSESSID',
                $phpSessionId,
                time() + $sessionLive,
                '/',
                explode(':', str_replace(['http://', 'https://'], "", ''))[0],
                false,
                true
            );
        }
    }
}