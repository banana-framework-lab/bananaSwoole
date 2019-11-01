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
    public $bindMap = null;

    /**
     * http请求的fd都会储存这里
     * @var array $httpMap
     */
    public $httpMap = null;

    /**
     * 初始化bindMap对象
     */
    public function instanceStart()
    {
        if (!$this->bindMap) {
            $this->bindMap = [];
        }
    }

    /**
     * fd绑定通道
     * @var int $fd
     * @param ChannelObject $channelObject
     */
    public function fdBindChannel(int $fd, ChannelObject $channelObject)
    {
        $this->bindMap["$fd"] = $channelObject;
    }

    /**
     * 获取通道对象
     * @param int $fd
     * @return ChannelObject|null
     */
    public function getChannelByFd(int $fd)
    {
        return $this->bindMap["$fd"] ?? null;
    }

    /**
     * fd解绑通道
     * @param int $fd
     */
    public function fdUnBindChannel(int $fd)
    {
        unset($this->bindMap["$fd"]);
    }

    /**
     * 设置fd为http请求
     * @param int $fd
     */
    public function pushFdInHttp(int $fd)
    {
        $this->httpMap["$fd"] = 1;
    }

    /**
     * @param $fd
     * @return bool
     */
    public function fdIsHttp($fd): bool
    {
        if (isset($this->httpMap["$fd"])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 取消fd为http请求
     * @param int $fd
     */
    public function popFdInHttp(int $fd)
    {
        unset($this->httpMap["$fd"]);
    }
}