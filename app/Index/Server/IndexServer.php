<?php
namespace App\Index\Server;

use Library\Abstracts\Server\AbstractSwooleServer;
use Swoole\WebSocket\Server as SwooleSocketServer;
use Throwable;

class IndexServer extends AbstractSwooleServer
{
    /**
     * @inheritDoc
     */
    public function onStart(SwooleSocketServer $server, int $workerId): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function exit(SwooleSocketServer $server, int $workerId)
    {
    }

    public function getExceptionResponse(Throwable $exception)
    {
        // TODO: Implement getExceptionResponse() method.
    }
}