<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/10/22
 * Time: 16:35
 */

namespace Library\Base\Server;

use Library\App\Server\DefaultFpmServer;
use Library\Config;
use Library\Virtual\Server\AbstractFpmServer;

/**
 * Class SwooleServer
 * @package Library\Server
 */
class BaseFpmServer
{
    /**
     * @var AbstractFpmServer $appServer
     */
    protected $appServer;

    /**
     * SwooleServer constructor.
     * @param AbstractFpmServer $appServer
     */
    public function __construct(AbstractFpmServer $appServer)
    {
        // Config初始化
        Config::instanceStart();

        // 非法初始化的类由默认server覆盖
        if (!$appServer) {
            $appServer = new DefaultFpmServer();
        }

        $this->appServer = $appServer;
    }
}