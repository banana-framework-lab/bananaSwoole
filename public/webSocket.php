<?php
/**
 * #平滑重启所有worker进程
 * kill -USR1 主进程PID
 */

use Library\Server\SwooleWebSocketServer;

require dirname(__FILE__) . '/../vendor/autoload.php';

// 运行web socket server
(new SwooleWebSocketServer())->run();