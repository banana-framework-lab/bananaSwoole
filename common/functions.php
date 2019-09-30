<?php
/**
 * 检查数据
 * @param $data
 * @return array|string
 */
function checkData($data)
{
    if (is_array($data)) {
        foreach ($data as $key => $v) {
            $data[$key] = checkData($v);
        }
    } else {
        if (!is_bool($data)) {
            //清楚左右空格
            $data = trim($data);
            //清楚html标签
            $data = strip_tags($data);
//            $data = htmlspecialchars($data);
//            $data = addslashes($data);
        }
    }
    return $data;
}

function post_curl($url, $post_data, $timeout = 5)
{
    $ch = curl_init();  //初始化curl
    curl_setopt($ch, CURLOPT_URL, $url);  //抓取指定网页
    curl_setopt($ch, CURLOPT_HEADER, 0);  //设置header
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  //设置不输出直接返回字符串
    curl_setopt($ch, CURLOPT_POST, 1);  //post提交方式
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_CAINFO, CURL_CERT_FILE_PATH);  //设置CA证书
    $result = curl_exec($ch);  //运行curl
    curl_close($ch);

    return $result;
}

function get_curl($url, $option = [])
{
    $ch = curl_init();  // 初始化curl
    curl_setopt($ch, CURLOPT_URL, $url);  // 抓取指定网页
    curl_setopt($ch, CURLOPT_HEADER, 0);  // 设置头文件的信息作为数据流输出
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  // 设置获取的信息以文件流的形式返回，而不是直接输出

    // 设置请求超时时间
    if (isset($option['timeout']) && $option['timeout'] > 1) {
        curl_setopt($ch, CURLOPT_TIMEOUT, $option['timeout']);
    }

    // 设置请求头信息
    if (isset($option['header']) && $option['header']) {
        $curlHeaders = [];
        foreach ($option['header'] as $key => $value) {
            $curlHeaders[] = "{$key}: $value";
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $curlHeaders);
    }

    curl_setopt($ch, CURLOPT_CAINFO, CURL_CERT_FILE_PATH);  //设置CA证书
    $result = curl_exec($ch);  //运行curl
    curl_close($ch);

    return $result;
}

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

/**
 * 判断是否是时间格式
 *
 * @param $dateTime
 * @return bool
 */
function is_date_time($dateTime)
{
    $ret = strtotime($dateTime);
    return $ret !== FALSE && $ret != -1;
}

/**
 * @param     $bytes
 * @param int $precision
 * @return string
 */
function to_size($bytes, $precision = 2)
{
    $rank = 0;
    $size = $bytes;
    $unit = "B";
    while ($size > 1024) {
        $size = $size / 1024;
        $rank++;
    }
    $size = round($size, $precision);
    switch ($rank) {
        case "1":
            $unit = "KB";
            break;
        case "2":
            $unit = "MB";
            break;
        case "3":
            $unit = "GB";
            break;
        case "4":
            $unit = "TB";
            break;
        default :

    }
    return $size . "" . $unit;
}

/**
 * 把秒数转换为时分秒的格式
 *
 * @param Int $times 时间，单位 秒
 * @return String
 */
function sec_to_time($times)
{
    $result = '00:00:00';
    if ($times > 0) {
        $hour = floor($times / 3600);
        $minute = floor(($times - 3600 * $hour) / 60);
        $second = floor((($times - 3600 * $hour) - 60 * $minute) % 60);
        $result = str_pad($hour, 2, 0, STR_PAD_LEFT)
            . ':' . str_pad($minute, 2, 0, STR_PAD_LEFT)
            . ':' . str_pad($second, 2, 0, STR_PAD_LEFT);
    }
    return $result;
}

/**
 * 读取/dev/urandom获取随机数
 * @param $len
 * @return mixed|string
 */
function randomFromDev($len)
{
    $fp = @fopen('/dev/urandom', 'rb');
    $result = '';
    if ($fp !== FALSE) {
        $result .= @fread($fp, $len);
        @fclose($fp);
    } else {
        trigger_error('Can not open /dev/urandom.');
    }
    // convert from binary to string
    $result = base64_encode($result);
    // remove none url chars
    $result = strtr($result, '+/', '-_');

    return substr($result, 0, $len);
}

/**
 * 重定向url
 */
function redirectUrl($url)
{
    $urlTemplate = "/^http(s)?:\\/\\/.+/";
    if (preg_match($urlTemplate, $url)) {
        header("Location:{$url}");
    } else {
        exit;
    }
}

/**
 * 把返回的数据集转换成Tree
 * @param $list
 * @param string $pk
 * @param string $pid
 * @param string $child
 * @param int $root
 * @return array
 */
function listToTree($list, $pk = 'id', $pid = 'pid', $child = 'children', $root = 0)
{
    // 创建Tree
    $tree = array();
    if (is_array($list)) {
        // 创建基于主键的数组引用
        $refer = array();
        foreach ($list as $key => $data) {
            $refer[$data[$pk]] =& $list[$key];
        }
        foreach ($list as $key => $data) {
            // 判断是否存在parent
            $parentId = $data[$pid];
            if ($root == $parentId) {
                $tree[] =& $list[$key];
            } else {
                if (isset($refer[$parentId])) {
                    $parent =& $refer[$parentId];
                    $parent[$child][] =& $list[$key];
                }
            }
        }
    }
    return $tree;
}


function getMillisecond()
{
    list($msec, $sec) = explode(' ', microtime());
    $msectime = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
    return $msectimes = substr($msectime, 0, 13);
}