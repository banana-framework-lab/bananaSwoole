<?php
/**
 * Created by PhpStorm.
 * User: ZhongHao-Zh
 * Date: 2019/10/28
 * Time: 22:36
 */

namespace Library\Container;

/**
 * Class ChannelObject
 * @package Library\Object
 */
class Channel
{
    /**
     * @var string $channel
     */
    private $channel;

    /**
     * @var string $handler
     */
    private $handler;

    /**
     * @return string
     */
    public function getChannel(): string
    {
        return $this->channel;
    }

    /**
     * @param string $channel
     */
    public function setChannel(string $channel)
    {
        $this->channel = $channel;
    }

    /**
     * @return string
     */
    public function getHandler(): string
    {
        return $this->handler;
    }

    /**
     * @param string $handler
     */
    public function setHandler(string $handler)
    {
        $this->handler = $handler;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'channel' => $this->channel,
            'handler' => $this->handler,
        ];
    }
}