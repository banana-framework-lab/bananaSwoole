<?php
/**
 * Created by PhpStorm.
 * User: ZhongHao-Zh
 * Date: 2019/10/28
 * Time: 22:04
 */

namespace Library\Virtual\Object;

abstract class AbstractMessageObject
{
    /**
     * @var string $channel 用户通道标识
     */
    public $channel;

    /**
     * @var int $toFd 发送到那个fd
     */
    public $toFd;
}