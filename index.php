<?php
/**
 * websocket服务入口文件
 * 热重启步骤：
 * 1. 找到此脚本主进程PID
 * 2. kill -USR1 主进程PID
 */

use App\Server\WebsocketServer;

require dirname(__FILE__) . '/config/init.php';
require dirname(__FILE__) . '/config/set.php';

date_default_timezone_set('PRC');
if (!DEBUG) {
    set_exception_handler('handle_exception');
}

$postJson = file_get_contents('php://input');
if ($postJson) {
    $request = checkData(json_decode($postJson, true));
    if (is_array($request)) {
        $_POST = array_merge($_POST, $request);
    }
}
if ($_GET) {
    $_GET = checkData($_GET);
}

// 运行websocket server
$server = new WebsocketServer();
$server->run();