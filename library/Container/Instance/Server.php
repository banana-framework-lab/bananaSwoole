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
        $port = Container::getConfig()->get("swoole.{$serverConfigIndex}.port", 9501);
//        $portStatus = trim(shell_exec("netstat -anp | grep \":{$port}\""));
//        if (!$portStatus) {
            $this->instance = new SwooleServer("0.0.0.0", $port, SWOOLE_PROCESS);
//        } else {
//            echo "{$port}已被占用" . PHP_EOL;
//            exit;
//        }
    }

    /**
     * @return SwooleServer
     */
    public function getSwooleServer()
    {
        return $this->instance;
    }

    /**
     * @param string $task_uri
     * @param array $data
     * @param int $taskId
     * @param null $callBack
     * @return mixed
     */
    public function pushTask(string $task_uri, array $data, int $taskId = -1, $callBack = null)
    {
        $task_data = $data;
        $task_data['task_uri'] = $task_uri;
        return $this->instance->task($task_data, $taskId, $callBack);
    }
}