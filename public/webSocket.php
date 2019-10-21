<?php
/**
 * #平滑重启所有worker进程
 * kill -USR1 主进程PID
 */


$lifeTime = 24 * 3600; // session有效期
session_set_cookie_params($lifeTime);
session_start();

require dirname(__DIR__) . '/config/init.php';
require dirname(__DIR__) . '/config/set.php';
require dirname(__DIR__) . '/config/key.php';


//开启php调试模式
if (DEBUG) {
    ini_set('display_errors', 'On');
    error_reporting(E_ALL);
}

use App\Server\WebsocketServer;


define('IS_POST', $_SERVER['REQUEST_METHOD'] == 'POST');

date_default_timezone_set('PRC');

// 运行websocket server
$server = new WebsocketServer();
$server->run();
