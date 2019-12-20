<?php
/**
 * Redis的Model抽象类
 * User: zzh
 * Date: 2018/10/10
 * Time: 17:28
 */

namespace Library\Virtual\Model\HttpModel;

/**
 * Class AbstractRedisModel
 * @package Library\Virtual\Model\HttpModel
 */
abstract class AbstractHttpModel
{
    public static $successCode = 1;

    public static $failCode = 0;

    /**
     * @param $url
     * @param $postData
     * @param int $timeout
     * @return string
     */
    function postCurl($url, $postData, $timeout = 5)
    {
        $ch = curl_init();  //初始化curl
        curl_setopt($ch, CURLOPT_URL, $url);  //抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0);  //设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  //设置不输出直接返回字符串
        curl_setopt($ch, CURLOPT_POST, 1);  //post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__) . "/../../../Cert/Curl/cacert.pem");  //设置CA证书
        $result = curl_exec($ch);  //运行curl
        curl_close($ch);

        return $result;
    }

    /**
     * @param $url
     * @param int $timeout
     * @return string
     */
    function getCurl($url, $timeout = 5)
    {
        $ch = curl_init();  //初始化curl
        curl_setopt($ch, CURLOPT_URL, $url);  //抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0);  //设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  //设置不输出直接返回字符串
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__) . "/../../../Cert/Curl/cacert.pem");  //设置CA证书
        $result = curl_exec($ch);  //运行curl
        curl_close($ch);

        return $result;
    }
}