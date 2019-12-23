<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/12/23
 * Time: 17:24
 */

use Library\Response;

/**
 * @param bool $return
 * @param string $type
 * @return mixed
 */
function helloBananaSwoole(bool $return = null, string $type = 'string')
{
    $lineChar = ($type == 'string') ? "\n" : '';
    $helloString = [];
    $helloString [] = " _                                   ____                     _     {$lineChar}";
    $helloString [] = "| |__   __ _ _ __   __ _ _ __   __ _/ ___|_      _____   ___ | | ___{$lineChar}";
    $helloString [] = "| '_ \ / _` | '_ \ / _` | '_ \ / _` \___ \ \ /\ / / _ \ / _ \| |/ _ \\$lineChar";
    $helloString [] = "| |_) | (_| | | | | (_| | | | | (_| |___) \ V  V / (_) | (_) | |  __/{$lineChar}";
    $helloString [] = "|_.__/ \__,_|_| |_|\__,_|_| |_|\__,_|____/ \_/\_/ \___/ \___/|_|\___|{$lineChar}";

    if ($return) {
        if ($type == 'string') {
            return implode('', $helloString);
        } else {
            return $helloString;
        }
    } else {
        echo $helloString;
        return '';
    }
}

/**
 * 打印栈
 * @param $content
 * @throws Error
 */
function dd($content)
{
    Response::dump(
        print_r(
            [
                'content' => $content,
                'trace' => debug_backtrace()
            ],
            true
        )
    );
    Response::exit();
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
    $pre_str = substr($pre_str, 0, -1);

    return $pre_str;
}
