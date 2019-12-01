<?php

use Library\App\Server\DefaultFpmServer;
use Library\Server\FpmServer;

date_default_timezone_set('PRC');
require dirname(__FILE__) . '/../vendor/autoload.php';

(new FpmServer(new DefaultFpmServer()))->run();
