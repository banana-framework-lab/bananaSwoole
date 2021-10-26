<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/11/22
 * Time: 20:25
 */

namespace Library\Abstracts\Server;

use Swoole\Table;
use Swoole\WebSocket\Server as SwooleSocketServer;
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
    abstract public function onStart(SwooleSocketServer $server, int $workerId): bool;

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