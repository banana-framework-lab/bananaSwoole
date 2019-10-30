<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/10/30
 * Time: 17:08
 */

namespace App\Api\Handler;

use Library\Virtual\Handler\AbstractHandler;
use Library\Virtual\Object\AbstractMessageObject;
use Swoole\Http\Request as SwooleHttpRequest;
use Swoole\WebSocket\Frame as SwooleSocketFrame;
use Swoole\WebSocket\Server as SwooleSocketServer;

class TestHandler extends AbstractHandler
{
    /**
     * 用户连接webSocket事件
     * @param SwooleSocketServer $server
     * @param SwooleHttpRequest $request
     */
    public function open(SwooleSocketServer $server, SwooleHttpRequest $request)
    {
        echo "1\n";
    }

    /**
     * 用户发送消息处理
     * @param SwooleSocketServer $server
     * @param SwooleSocketFrame $frame
     */
    public function message(SwooleSocketServer $server, SwooleSocketFrame $frame)
    {
        echo "2\n";
    }

    /**
     * 用户关闭webSocket事件
     * @param SwooleSocketServer $server
     * @param int $fd
     */
    public function close(SwooleSocketServer $server, int $fd)
    {
        echo "3\n";
    }


    /**
     * 用户发消息消化
     * @param AbstractMessageObject $messageObject
     */
    public function consume(AbstractMessageObject $messageObject)
    {
        echo "4\n";
    }
}