<?php
/**
 * Created by PhpStorm.
 * User: ZhongHao-Zh
 * Date: 2019/10/28
 * Time: 22:36
 */

namespace Library\Object;

/**
 * Class ChannelObject
 * @package Library\Object
 */
class ChannelObject
{
    /**
     * @var string $channel
     */
    private $channel;

    /**
     * @var string $event
     */
    private $event;

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
    public function getEvent(): string
    {
        return $this->event;
    }

    /**
     * @param string $event
     */
    public function setEvent(string $event)
    {
        $this->event = $event;
    }
}