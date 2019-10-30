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
     * @var array $channelPool
     */
    private static $channelPool = [];

    /**
     * 初始化Router类
     */
    public static function instanceStart()
    {
        $handler = opendir(dirname(__FILE__) . '/../channel');
        while (($fileName = readdir($handler)) !== false) {
            if ($fileName != "." && $fileName != "..") {
                $fileData = require dirname(__FILE__) . '/../channel/' . $fileName;
                self::$channelPool = array_merge(self::$channelPool, $fileData);
            }
        }
        closedir($handler);
    }

    /**
     * 初始化Router类
     * @param array $requestData
     * @return ChannelObject
     */
    public static function route(array $requestData): ChannelObject
    {
        $requestChannel = $requestData['channel'] ?? 'Api';
        $channel = self::$channelPool[$requestChannel] ?? null;
        if (is_null($channel)) {
            $channel = (isset($requestData['channel']) && $requestData['channel']) ? $requestData['channel'] : 'Api';
            $handler = (isset($requestData['handler']) && $requestData['handler']) ? $requestData['handler'] : 'Index';
            $channelObject = new ChannelObject();
            $channelObject->setChannel($channel);
            $channelObject->setHandler("\\App\\{$channel}\\Handler\\{$handler}Handler");
            return $channelObject;
        } else {
            $channelObject = new ChannelObject();
            $channelObject->setChannel($requestChannel);
            $channelObject->setHandler($channel);
            return $channelObject;
        }
    }

    /**
     * 返回通道列表
     * @return array
     */
    public static function getChannelList(): array
    {
        return array_keys(self::$channelPool);
    }
}