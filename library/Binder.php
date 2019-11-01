<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/10/29
 * Time: 12:05
 */

namespace Library;

use Library\Object\ChannelObject;

class Binder
{
    /**
     * websocket连接的fd都会储存在这里
     * @var array $bindMap
     */
    public static $bindMap = null;

    /**
     * http请求的fd都会储存这里
     * @var array $httpMap
     */
    public static $httpMap = null;

    /**
     * 初始化bindMap对象
     */
    public static function instanceStart()
    {
        if (!self::$bindMap) {
            self::$bindMap = [];
        }
    }

    /**
     * fd绑定通道
     * @var int $fd
     * @param ChannelObject $channelObject
     */
    public static function fdBindChannel(int $fd, ChannelObject $channelObject)
    {
        self::$bindMap["$fd"] = $channelObject;
    }

    /**
     * 获取通道对象
     * @param int $fd
     * @return ChannelObject|null
     */
    public static function getChannelByFd(int $fd)
    {
        return self::$bindMap["$fd"] ?? null;
    }

    /**
     * fd解绑通道
     * @param int $fd
     */
    public static function fdUnBindChannel(int $fd)
    {
        unset(self::$bindMap["$fd"]);
    }

    /**
     * 设置fd为http请求
     * @param int $fd
     */
    public static function pushFdInHttp(int $fd)
    {
        self::$httpMap["$fd"] = 1;
    }

    /**
     * @param $fd
     * @return bool
     */
    public static function fdIsHttp($fd): bool
    {
        if (isset(self::$httpMap["$fd"])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 取消fd为http请求
     * @param int $fd
     */
    public static function popFdInHttp(int $fd)
    {
        unset(self::$httpMap["$fd"]);
    }
}