<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/10/28
 * Time: 20:22
 */

namespace Library;

use Library\Object\WebSocket\SocketGetDataObject;

class Channel
{
    /**
     * 初始化Router类
     * @param SocketGetDataObject $getData
     * @return string
     */
    public static function route(SocketGetDataObject $getData)
    {
        return "\\App\\{$getData->channel}\\Event\\{$getData->event}Event";
    }
}