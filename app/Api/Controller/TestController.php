<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/9/5 0005
 * Time: 16:28
 */

namespace App\Api\Controller;


use Co;
use Library\Entity\Swoole\EntitySwooleRequest;
use Library\Helper\LogHelper;
use Library\Virtual\Controller\AbstractController;
use Swoole\Coroutine;

class TestController extends AbstractController
{
    public function index()
    {
        $start = json_encode(EntitySwooleRequest::server('request_time_float'));
        Co::sleep(3.0);
        $string =  'serverName '.EntitySwooleRequest::server('server_name').' cid ' . Coroutine::getuid() . '  start' . $start . '  end' . json_encode(EntitySwooleRequest::server('request_time_float')) . "";
        LogHelper::info($string);
    }
}