<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/10/30
 * Time: 19:44
 */

namespace App\Api\Object;

use Library\Virtual\Object\AbstractMessageObject;

class MessageObject extends AbstractMessageObject
{
    public $message = '';

    /**
     * MessageObject constructor.
     * @param int $toFd
     * @param string $channel
     * @param string $message
     */
    public function __construct(int $toFd, string $channel, string $message)
    {
        $this->toFd = $toFd;
        $this->channel = $channel;
        $this->message = $message;
    }
}