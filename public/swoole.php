<?php
use Library\App\Server\DefaultSwooleServer;
use Library\Server\BananaSwooleServer;

date_default_timezone_set('PRC');
require dirname(__FILE__) . '/../vendor/autoload.php';

$adminServer = new BananaSwooleServer('server');
$adminServer->setServer(new DefaultSwooleServer());
$adminServer->run();