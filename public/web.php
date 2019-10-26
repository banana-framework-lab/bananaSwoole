<?php
/**
 * 平滑重启所有worker进程
 * kill -USR1 master的PID
 */

use Library\Server\SwooleWebServer;

require dirname(__FILE__) . '/../vendor/autoload.php';

// 运行http server
$server = new SwooleWebServer();
$server->run();
