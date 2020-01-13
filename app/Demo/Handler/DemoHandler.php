<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/10/30
 * Time: 17:08
 */

namespace App\Api\Handler;

use App\Api\Object\MessageObject;
use Library\Entity\Swoole\EntitySwooleServer;
use Library\Virtual\Handler\AbstractHandler;
use Library\Virtual\Object\AbstractMessageObject;
use Swoole\Http\Request as SwooleHttpRequest;
use Swoole\WebSocket\Frame as SwooleSocketFrame;
use Swoole\WebSocket\Server as SwooleSocketServer;

class DemoHandler extends AbstractHandler
{
    /**
     * 用户连接webSocket事件
     * @param SwooleSocketServer $server
     * @param SwooleHttpRequest $request
     */
    public function open(SwooleSocketServer $server, SwooleHttpRequest $request)
    {
        echo "open function\n";
    }

    /**
     * 用户发送消息处理
     * @param SwooleSocketServer $server
     * @param SwooleSocketFrame $frame
     */
    public function message(SwooleSocketServer $server, SwooleSocketFrame $frame)
    {
        echo "message function\n";
    }

    /**
     * 用户关闭webSocket事件
     * @param SwooleSocketServer $server
     * @param int $fd
     */
    public function close(SwooleSocketServer $server, int $fd)
    {
        echo "close function\n";
    }


    /**
     * 用户发消息消化
     * @param AbstractMessageObject $messageObject
     */
    public function consume(AbstractMessageObject $messageObject)
    {
        /* @var MessageObject $messageBody */
        $messageBody = $messageObject;
        EntitySwooleServer::getInstance()->push($messageObject->toFd, $messageBody->message);
    }
}