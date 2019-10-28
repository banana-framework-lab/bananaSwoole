<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/10/28
 * Time: 12:02
 */

namespace Library\Object\WebSocket;

/**
 * Class SocketGetData
 * @package Library\Object\WebSocket
 */
class SocketGetDataObject
{
    /**
     * @var string $id 用户唯一标识
     */
    public $id;

    /**
     * @var string $appId 用户所属app的id
     */
    public $appId;

    /**
     * @var string $channel 用户通道标识
     */
    public $channel;

    /**
     * @var string $event 用户事件标识
     */
    public $event;

    /**
     * @var int $fd 用户的fd
     */
    public $fd;

    /**
     * @var string $username 用户的名字
     */
    public $username;

    /**
     * @var string $secret 连接的秘钥
     */
    public $secret;

    /**
     * @var int $time 连接的时间戳
     */
    public $time;

    /**
     * 构造方法可以初始化对象属性值
     *
     * @param array $property
     */
    public function __construct($property = [])
    {
        // 设置属性值
        foreach ($property as $propertyName => $propertyValue) {
            if (property_exists($this, $propertyName)) {
                if (!is_array($propertyValue)) {
                    if (trim($propertyValue) !== "") $this->$propertyName = $propertyValue;
                } else {
                    if (!empty($propertyValue)) $this->$propertyName = $propertyValue;
                }
            }
        }
    }
}