<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/12/23
 * Time: 17:24
 */

use Library\Container;
use Swoole\Coroutine;

const C_EXIT_CODE = 444;

/**
 * @param bool $return
 * @param string $type
 * @return array|string
 */
function bananaSwoole(bool $return = true, string $type = 'string')
{
    switch ($type) {
        case 'string':
            $lineChar = PHP_EOL;
            break;
        case 'web':
            $lineChar = '<br>';
            break;
        default:
            $lineChar = '';
            break;
    }

    $helloString = [];
    $helloString [] = " _                                   ____                     _     $lineChar";
    $helloString [] = "| |__   __ _ _ __   __ _ _ __   __ _/ ___|_      _____   ___ | | ___$lineChar";
    $helloString [] = "| '_ \ / _` | '_ \ / _` | '_ \ / _` \___ \ \ /\ / / _ \ / _ \| |/ _ \\$lineChar";
    $helloString [] = "| |_) | (_| | | | | (_| | | | | (_| |___) \ V  V / (_) | (_) | |  __/$lineChar";
    $helloString [] = "|_.__/ \__,_|_| |_|\__,_|_| |_|\__,_|____/ \_/\_/ \___/ \___/|_|\___|$lineChar";

    if ($return) {
        if ($type === 'string') {
            return implode('', $helloString);
        } elseif ($type === 'web') {
            $content = implode('', $helloString);
            $content = str_replace(' ', '&nbsp;', $content);
            return "<!DOCTYPE html><html lang='zh'><body style='font-family:cursive;'><div style='width:555px;text-align:center;font-size: 35px;'>welcome to use</div></div><div style='width:555px;'>$content</div></body></html>";
        } else {
            return $helloString;
        }
    } else {
        echo implode('', $helloString);;
        return '';
    }
}

/**
 * 打印栈
 * @param $content
 */
function c_var_dump($content)
{
    $workerId = Container::getServer()->getSwooleServer()->worker_id;
    $cId = Coroutine::getCid();
    Container::getResponse()->dump($content, $workerId, $cId);
    Container::getResponse()->exit();
}

/**
 * 退出协程
 */
function c_exit()
{
    Container::getResponse()->exit();
}

/**
 * 生成一个不url转码的请求字符串
 * @param $param
 * @return bool|string
 */
function build_query_no_encode($param)
{
    $pre_str = '';
    foreach ($param as $key => $val) {
        $pre_str .= $key . '=' . $val . '&';
    }
    //去掉最后一个&字符
    return substr($pre_str, 0, -1);
}

function camelize($unCamelizeWords, $separator = '_')
{
    return str_replace($separator, '', lcfirst(ucwords(strtolower($unCamelizeWords), $separator)));
}

function daemonize()
{
    if (pcntl_fork()) {
        exit;
    }

    if (posix_setsid() < 0) {
        exit;
    }

    return posix_getpid();
}

//返回当前的毫秒时间戳
function microTimes()
{
    [$msec, $sec] = explode(' ', microtime());
    $msectime = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
    return $msectime;
}
