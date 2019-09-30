<?php
namespace App\Server\Config;
/**
 * Created by PhpStorm.
 * User: zzh
 * Date: 2019/8/23
 * Time: 15:20
 */
class MessageType
{
    /**
     * 发送常规消息类型
     */
    const TO_MESSAGE = 10001;

    /**
     * 发送常规消息类型
     */
    const FROM_MESSAGE = 10002;

    /**
     * 关闭websocket的客户端类型
     */
    const CLOSE = 10004;
}