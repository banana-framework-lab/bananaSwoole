<?php

namespace Library\Server;

use Library\Base\Server\BaseFpmServer;
use Library\Config;
use Library\Virtual\Server\AbstractFpmServer;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;

/**
 * Class SwooleWebSocketServer
 * @package Library\Server
 */
class FpmServer extends BaseFpmServer
{
    /**
     * SwooleWebSocketServer constructor.
     * @param AbstractFpmServer $appServer
     */
    public function __construct(AbstractFpmServer $appServer)
    {
        parent::__construct($appServer);
    }

    /**
     * 处理Http的请求
     */
    public function run()
    {
        $allowOrigins = Config::get('app.allow_origin', []);

        if (isset($_SERVER['HTTP_ORIGIN']) && in_array(strtolower($_SERVER['HTTP_ORIGIN']), $allowOrigins)) {
            if (in_array(strtolower($_SERVER['HTTP_ORIGIN']), $allowOrigins)) {
                header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']); //支持全域名访问，不安全，部署后需要固定限制为客户端网址
            }
            header("Access-Control-Allow-Credentials: true");
            header('Access-Control-Allow-Methods:POST,GET,OPTIONS,DELETE'); //支持的http 动作
            header('Access-Control-Allow-Headers:x-requested-with,User-Platform,Content-Type,X-Token');  //响应头 请按照自己需求添加。
        }
        $this->appServer->request();
    }
}