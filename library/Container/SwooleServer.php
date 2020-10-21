<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/11/1
 * Time: 13:56
 */

namespace Library\Container;

use Library\Container;
use Library\Exception\LogicException;
use Swoole\WebSocket\Server;

class SwooleServer
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
        $this->instance = new Server(
            "0.0.0.0",
            Container::getConfig()->get("swoole.{$serverConfigIndex}.port", 9501),
            SWOOLE_PROCESS
        );
    }

    /**
     * @return Server
     */
    public function getSwooleServer()
    {
        return $this->instance;
    }
}