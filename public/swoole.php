<?php
use Library\App\Server\DefaultSwooleServer;
use Library\Server\SwooleServer;

date_default_timezone_set('PRC');
require dirname(__FILE__) . '/../vendor/autoload.php';

$adminServer = new SwooleServer();
$adminServer->setConfigIndex('server');
$adminServer->setServer(new DefaultSwooleServer());
$adminServer->run();