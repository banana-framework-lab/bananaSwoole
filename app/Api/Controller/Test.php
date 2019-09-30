<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/9/5 0005
 * Time: 16:28
 */

namespace App\Api\Controller;

use App\Library\Entity\Log;
use App\Library\Entity\Swoole\Request;
use App\Library\Virtual\Controller\AbstractController;
use Co;
use Swoole\Coroutine;

class Test extends AbstractController
{
    public function index()
    {
        $start = json_encode(Request::server('request_time_float'));
        co::sleep(3.0);
        $string =  'serverName '.Request::server('server_name').' cid ' . Coroutine::getuid() . '  start' . $start . '  end' . json_encode(Request::server('request_time_float')) . "";
        Log::info($string);
    }
}