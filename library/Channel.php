<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/10/28
 * Time: 20:22
 */

namespace Library;

use Library\Object\ChannelObject;

/**
 * Class Channel
 * @package Library
 */
class Channel
{
    /**
     * 初始化Router类
     * @param array $requestData
     * @return ChannelObject
     */
    public static function route(array $requestData): ChannelObject
    {
        $channel = (isset($requestData['channel']) && $requestData['channel']) ? $requestData['channel'] : 'Api';
        $handler = (isset($requestData['handler']) && $requestData['handler']) ? $requestData['handler'] : 'Index';
        $channelObject = new ChannelObject();
        $channelObject->setChannel($channel);
        $channelObject->setHandler("\\App\\{$channel}\\Handler\\{$handler}Handler");
        return $channelObject;
    }
}