<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/10/28
 * Time: 20:22
 */

namespace Library;

use Library\Object\ChannelObject;

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
        $event = (isset($requestData['event']) && $requestData['event']) ? $requestData['event'] : 'Api';
        $channelObject = new ChannelObject();
        $channelObject->setChannel($channel);
        $channelObject->setEvent("\\App\\{$channel}\\Event\\{$event}Event");
        return $channelObject;
    }
}