<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/9/5 0005
 * Time: 16:28
 */

namespace App\Api\Controller;

use App\Api\Logic\DemoLogic;
use App\Api\Service\DemoService;
use Library\Helper\LogHelper;
use Library\Request;
use Library\Response;
use Library\Virtual\Controller\AbstractController;
use Swoole\Coroutine;

class DemoController extends AbstractController
{
    public function demoLog()
    {
        $start = json_encode(Request::server('request_time_float'));
        Coroutine::sleep(3.0);
        $string = ' cid ' . Coroutine::getuid() . '  start' . $start . '  end' . json_encode(Request::server('request_time_float')) . "";
        LogHelper::info($string, ['msg' => 'demoLog记录日志']);
    }

    public function demoFpm()
    {
        $string = 'Fpm Success ' . time();
        return ['msg' => $string];
    }

    public function demoHelloWorld()
    {
        Response::json(['msg' => 'hello world']);
    }

    public function demoLogic()
    {
        $return = (new DemoLogic())->demoLogic();
        return $return;
    }

    public function demoLogicForModel()
    {
        $return = (new DemoLogic())->demoLogicForModel();
        return $return;
    }

    public function demoForDd()
    {
        dd('这是测试dd打印的字符串');
    }

    public function demoForMiddleWare()
    {
        return ['data' => $this->request];
    }

    public function demoForService()
    {
        return ['data' => DemoService::getNoAuthCode()];
    }
}