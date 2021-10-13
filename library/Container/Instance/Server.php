<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/11/1
 * Time: 13:56
 */

namespace Library\Container\Instance;

use Library\Container;
use Swoole\WebSocket\Server as SwooleServer;

class Server
{
    /**
     * @var Server $instance
     */
    private $instance = null;

    /**
     * SwooleServer constructor.
     * @param string $serverConfigIndex
     */
    public function __construct(string $serverConfigIndex)
    {
        $this->instance = new SwooleServer(
            "0.0.0.0",
            Container::getConfig()->get("swoole.{$serverConfigIndex}.port", 9501),
            SWOOLE_PROCESS
        );
    }

    /**
     * @return SwooleServer
     */
    public function getSwooleServer()
    {
        return $this->instance;
    }
}