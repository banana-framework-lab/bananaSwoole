<?php


namespace Library\Server\Functions;


class WorkStartEcho
{
    public $serverType = '';

    public $echoWidth = 75;

    public $port;

    public $taskNum;

    public $workerNum;

    public $xChar;

    public $yChar;

    /**
     * worker启动完成后报的程序信息
     * @param $server
     * @param $autoReload
     */
    public function main($server, $autoReload)
    {
        $logo = bananaSwoole(true, 'array');
        $startDateTime = '';
        $startDateTime = date('Y-m-d H:i:s');

        echo PHP_EOL;
        foreach ($logo as $key => $value) {
            echo ' ' . str_pad("{$value}", $this->echoWidth - 2, ' ', STR_PAD_BOTH) . " " . PHP_EOL;
        }
        echo PHP_EOL;
        echo str_pad("", $this->echoWidth, $this->xChar, STR_PAD_BOTH) . PHP_EOL;
        echo $this->yChar . str_pad("$this->serverType start", $this->echoWidth - 2, ' ', STR_PAD_BOTH) . "$this->yChar" . PHP_EOL;
        echo str_pad("", $this->echoWidth, $this->xChar, STR_PAD_BOTH) . PHP_EOL;
        echo $this->yChar . str_pad("", $this->echoWidth - 2, ' ', STR_PAD_BOTH) . "$this->yChar" . PHP_EOL;
        echo $this->yChar . str_pad("listen_ip: 0.0.0.0  listen_port: {$this->port}  address: //0.0.0.0:{$this->port}", $this->echoWidth - 2, ' ', STR_PAD_BOTH) . "$this->yChar" . PHP_EOL;
        echo $this->yChar . str_pad("", $this->echoWidth - 2, ' ', STR_PAD_BOTH) . "$this->yChar" . PHP_EOL;
        echo $this->yChar . str_pad("manage_pid: {$server->manager_pid}      master_pid: {$server->master_pid}      worker_number: {$this->workerNum}", $this->echoWidth - 2, ' ', STR_PAD_BOTH) . "$this->yChar" . PHP_EOL;
        echo $this->yChar . str_pad("", $this->echoWidth - 2, ' ', STR_PAD_BOTH) . "$this->yChar" . PHP_EOL;
        echo $this->yChar . str_pad("autoHotReloadId: {$autoReload->reloadTickId}   task_number: {$this->taskNum}   time: {$startDateTime}", $this->echoWidth - 2, ' ', STR_PAD_BOTH) . "$this->yChar" . PHP_EOL;
        echo $this->yChar . str_pad("", $this->echoWidth - 2, ' ', STR_PAD_BOTH) . "$this->yChar" . PHP_EOL;
        echo str_pad("", $this->echoWidth, $this->xChar, STR_PAD_BOTH) . PHP_EOL;
        echo PHP_EOL;
    }
}