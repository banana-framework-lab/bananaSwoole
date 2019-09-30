<?php

namespace App\Library\Service;

/**
 * Created by PhpStorm.
 * User: zzh
 * Date: 2019/4/23
 * Time: 14:12
 */
class PlatformService
{
    /**
     * 静态对象
     * @var null
     */
    protected static $instance = null;

    protected static $platformInfo = [
        '13090' => '45f26b9e9d7090c17fc555ed22e655ec'
    ];

    /**
     * 获取实例
     * @return null|static
     */
    public static function instance()
    {
        if (empty(static::$instance)) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    public function judgePlatform($id, $secret)
    {
        return self::$platformInfo[$id] == $secret;
    }
}