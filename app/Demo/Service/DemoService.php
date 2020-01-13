<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/11/15
 * Time: 20:49
 */

namespace App\Api\Service;

class DemoService
{
    public static function getNoLoginCode()
    {
        return 10000;
    }

    public static function getNoAuthCode()
    {
        return 10001;
    }
}