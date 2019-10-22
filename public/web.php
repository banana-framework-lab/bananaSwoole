<?php
/**
 * #平滑重启所有worker进程
 * kill -USR1 主进程PID
 */

use Library\Sever\SwooleWebServer;

// 运行http server
$server = new SwooleWebServer();
$server->run();
