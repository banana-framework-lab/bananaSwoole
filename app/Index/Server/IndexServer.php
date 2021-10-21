<?php


namespace App\Index\Server;


use Library\Abstracts\Server\AbstractSwooleServer;
use Swoole\WebSocket\Server as SwooleSocketServer;

class IndexServer extends AbstractSwooleServer
{
    /**
     * @inheritDoc
     */
    public function onStart(SwooleSocketServer $server, int $workerId): bool
    {
        // TODO: Implement start() method.
        return true;
    }

    /**
     * @inheritDoc
     */
    public function exit(SwooleSocketServer $server, int $workerId)
    {
        // TODO: Implement exit() method.
    }
}