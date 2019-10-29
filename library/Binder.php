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
     * @var array $bindMap
     */
    public static $bindMap = null;

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
}