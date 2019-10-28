<?php

namespace Library;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/10/28
 * Time: 10:51
 */

use Library\Object\WebSocket\SocketGetDataObject;
use ReflectionClass;
use Swoole\Http\Request as SwooleHttpRequest;

/**
 * Class Validate
 */
class Validate
{
    /**
     * @var array $openNeedParam
     */
    private static $openNeedParam = [];

    /**
     * 初始化validate的规则
     * @throws \ReflectionException
     */
    public static function instanceStart()
    {
        $SocketUserObjectProps = (new ReflectionClass(SocketGetDataObject::class))->getProperties();
        foreach ($SocketUserObjectProps as $key => $value) {
            self::$openNeedParam[] = $value->getName();
        }
    }

    /**
     * 检查用户连接socket时的参数
     * @param SocketGetDataObject $getData
     * @return bool
     */
    public static function checkSocketOpen(SocketGetDataObject $getData): bool
    {
        foreach (self::$openNeedParam as $key => $value) {
            if (!is_null($getData->{$value})) {
                return false;
            }
        }
        return true;
    }

    /**
     * 检查用户连接socket时的秘钥
     * @param SocketGetDataObject $getData
     * @return bool
     */
    public static function checkSocketOpenSecret(SocketGetDataObject $getData): bool
    {
        return true;
    }
}