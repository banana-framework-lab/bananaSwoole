<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/9/5 0005
 * Time: 16:28
 */

namespace App\Api\Controller;


use App\Api\Logic\TestLogic;
use Co;
use Exception;
use Library\Helper\LogHelper;
use Library\Helper\RequestHelper;
use Library\Helper\ResponseHelper;
use Library\Virtual\Controller\AbstractController;
use Swoole\Coroutine;

class TestController extends AbstractController
{
    public function testLog()
    {
        $start = json_encode(RequestHelper::server('request_time_float'));
        Co::sleep(3.0);
        $string = 'serverName ' . RequestHelper::server('server_name') . ' cid ' . Coroutine::getuid() . '  start' . $start . '  end' . json_encode(EntitySwooleRequest::server('request_time_float')) . "";
        LogHelper::info($string, ['msg' => 'swoole并发测试']);
    }

    public function index()
    {
        ResponseHelper::json(['msg' => 'hello world']);
    }

    /**
     */
    public function indexError()
    {
        try {
            $this->test(1);
        } catch (Exception $e) {
            echo 1;
        }
//        var_dump(RequestHelper::getInstance());
//        var_dump(ResponseHelper::getInstance());
//        var_dump(Router::getRouteInstance());


    }

    private function test(array $a)
    {

    }

    public function login()
    {
        $return = (new TestLogic())->login('zhangzhonghao', '123456');
        ResponseHelper::json(['data' => $return]);
    }

    public function getNumber()
    {
        $return = (new TestLogic())->getNumber();
        ResponseHelper::json(['data' => $return]);
    }

    public function testVarDump()
    {
//        ResponseHelper::dump([1, 2, 3, 4, 5, 6, 7, 8]);
//        ResponseHelper::dump([1, 2, 3, 4, 5, 6, 7, 8]);
        ResponseHelper::json(['msg' => 'success']);
//        ResponseHelper::exit();
    }
}