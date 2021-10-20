<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/10/28
 * Time: 20:22
 */

namespace Library\Container\Instance;

use Library\Container\Channel;

/**
 * Class Channel
 * @package Library
 */
class ChannelMap
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
                if (file_exists(dirname(__FILE__) . '/../channel/' . $fileName)) {
                    $fileData = require dirname(__FILE__) . '/../channel/' . $fileName;
                    self::$channelPool = array_merge(self::$channelPool, $fileData);
                }
            }
        }
        closedir($handler);
    }

    /**
     * 初始化Router类
     * @param array $requestData
     * @return Channel
     */
    public static function route(array $requestData): Channel
    {
        $requestChannel = $requestData['channel'] ?? 'Index';
        $channel = self::$channelPool[$requestChannel] ?? null;
        if (is_null($channel)) {
            $channel = (isset($requestData['channel']) && $requestData['channel']) ? $requestData['channel'] : 'Index';
            $handler = (isset($requestData['handler']) && $requestData['handler']) ? $requestData['handler'] : 'Index';
            $channelObject = new Channel();
            $channelObject->setChannel($channel);
            $channelObject->setHandler("\\App\\{$channel}\\Handler\\{$handler}Handler");
        } else {
            $channelObject = new Channel();
            $channelObject->setChannel($requestChannel);
            $channelObject->setHandler($channel);
        }
        return $channelObject;
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