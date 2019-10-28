<?php
/**
 * Created by PhpStorm.
 * User: ZhongHao-Zh
 * Date: 2019/10/28
 * Time: 22:04
 */

namespace Library\Virtual\Object;

abstract class AbstractSocketFrame
{
    /**
     * @var string $channel 用户通道标识
     */
    public $channel;

    /**
     * @var string $appId 用户所属app的id
     */
    public $appId;

    /**
     * @var string $secret 连接的秘钥
     */
    public $secret;

    /**
     * @var int $time 连接的时间戳
     */
    public $time;

    /**
     * 设置字段值
     * @param array $property
     */
    abstract public function setField(array $property = []);
}