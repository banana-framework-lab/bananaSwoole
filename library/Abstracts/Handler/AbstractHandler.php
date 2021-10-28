<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/10/28
 * Time: 16:29
 */

namespace Library\Abstracts\Handler;

use Swoole\Http\Request as SwooleHttpRequest;
use Swoole\WebSocket\Server as SwooleSocketServer;
use Swoole\WebSocket\Frame as SwooleSocketFrame;

/**
 * Class Handler
 * @package Library\Abstracts\Handler
 */
abstract class AbstractHandler
{
    /**
     * AbstractHandler constructor.
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
     * 用户发送消息处理
     * @param SwooleSocketServer $server
     * @param SwooleSocketFrame $frame
     */
    abstract public function message(SwooleSocketServer $server, SwooleSocketFrame $frame);

    /**
     * 用户关闭webSocket事件
     * @param SwooleSocketServer $server
     * @param int $fd
     */
    abstract public function close(SwooleSocketServer $server, int $fd);
}