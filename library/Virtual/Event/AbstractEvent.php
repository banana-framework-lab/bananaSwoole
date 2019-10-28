<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/10/28
 * Time: 16:29
 */

namespace Library\Virtual\Event;

use Swoole\Http\Request as SwooleHttpRequest;
use Swoole\WebSocket\Server as SwooleSocketServer;

/**
 * Class AbstractEvent
 * @package Library\Virtual\Event
 */
abstract class AbstractEvent
{
    /**
     * AbstractEvent constructor.
     */
    public function __construct()
    {
    }

    /**
     * 用户连接webSocket事件
     * @param SwooleSocketServer $server
     * @param SwooleHttpRequest $request
     */
    abstract public function open(SwooleSocketServer $server, SwooleHttpRequest $request);

    /**
     * @return mixed
     */
    abstract public function message();

    /**
     * @return mixed
     */
    abstract public function close();
}