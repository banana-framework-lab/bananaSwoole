<?php

use Library\App\Server\DefaultSwooleServer;
use Library\Server\SwooleServer;

date_default_timezone_set('PRC');
require dirname(__FILE__) . '/../vendor/autoload.php';

(new SwooleServer())->init(new DefaultSwooleServer())->run();