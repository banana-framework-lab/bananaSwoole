<?php

namespace Library\Server;

use Library\Config;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/10/22
 * Time: 16:35
 */
class SwooleServer
{
    public function __construct()
    {
        // Config初始化
        Config::instanceStart();
    }

}