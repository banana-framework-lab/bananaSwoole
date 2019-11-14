<?php
/**
 * 平滑重启所有worker进程
 * kill -USR1 master的PID
 */

use Library\Server\SwooleWebServer;

date_default_timezone_set('PRC');
require dirname(__FILE__) . '/../vendor/autoload.php';

// 运行http server
(new SwooleWebServer())->run();
