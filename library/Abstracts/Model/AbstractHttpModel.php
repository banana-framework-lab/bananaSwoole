<?php
/**
 * Http的Model抽象类
 * User: zzh
 * Date: 2018/10/10
 * Time: 17:28
 */

namespace Library\Abstracts\Model;

/**
 * Class AbstractRedisModel
 * @package Library\Abstracts\Model\HttpModel
 */
abstract class AbstractHttpModel
{
    /**
     * 成功的请求码
     * @var int $successCode
     */
    public static $successCode = 1;

    /**
     * 失败的请求码
     * @var int $failCode
     */
    public static $failCode = 0;

    /**
     * POST请求
     * @param string $url
     * @param $postData
     * @param int $timeout
     * @param array $headers
     * @return string
     */
    function postCurl(string $url, $postData, int $timeout = 5, array $headers = []): string
    {
        $ch = curl_init();  //初始化curl
        curl_setopt($ch, CURLOPT_URL, $url);  //抓取指定网页
        if ($headers) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        curl_setopt($ch, CURLOPT_HEADER, 0);  //设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  //设置不输出直接返回字符串
        curl_setopt($ch, CURLOPT_POST, 1);  //post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__) . "/../../Common/Cert/cacert.pem");  //设置CA证书
        $result = curl_exec($ch);  //运行curl
        curl_close($ch);

        return $result;
    }

    /**
     * GET请求
     * @param $url
     * @param int $timeout
     * @param array $headers
     * @return string
     */
    function getCurl($url, int $timeout = 5, array $headers = []): string
    {
        $ch = curl_init();  //初始化curl
        curl_setopt($ch, CURLOPT_URL, $url);  //抓取指定网页
        if ($headers) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        curl_setopt($ch, CURLOPT_HEADER, 0);  //设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  //设置不输出直接返回字符串
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__) . "/../../Common/Cert/cacert.pem");  //设置CA证书

        $result = curl_exec($ch);  //运行curl
        curl_close($ch);

        return $result;
    }

    /**
     * GET获取HTTP返回码
     * @param $url
     * @param int $timeout
     * @return mixed
     */
    static public function getHttpStatus($url, int $timeout = 5)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url); //设置URL
        curl_setopt($curl, CURLOPT_HTTPHEADER, ["Cache-Control: no-cache"]);
        curl_setopt($curl, CURLOPT_HEADER, 1); //获取Header
        curl_setopt($curl, CURLOPT_NOBODY, true); //不要Body
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($curl, CURLOPT_CAINFO, dirname(__FILE__) . "/../../../Cert/Curl/cacert.pem");  //设置CA证书
        curl_exec($curl); //开始执行啦～
        $return = curl_getinfo($curl, CURLINFO_HTTP_CODE); //HTTP STATUS码

        curl_close($curl); //用完记得关掉他

        return $return;
    }

    /**
     * POST获取HTTP返回码
     * @param $url
     * @param $postData
     * @param int $timeout
     * @return mixed
     */
    static public function postHttpStatus($url, $postData, int $timeout = 5)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url); //设置URL
        curl_setopt($curl, CURLOPT_HTTPHEADER, ["Cache-Control: no-cache"]);
        curl_setopt($curl, CURLOPT_HEADER, 1); //获取Header
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);  //设置不输出直接返回字符串
        curl_setopt($curl, CURLOPT_POST, 1);  //post提交方式
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($curl, CURLOPT_CAINFO, dirname(__FILE__) . "/../../../Cert/Curl/cacert.pem");  //设置CA证书
        curl_exec($curl); //开始执行啦～
        $return = curl_getinfo($curl, CURLINFO_HTTP_CODE); //HTTP STATUS码

        curl_close($curl); //用完记得关掉他

        return $return;
    }
}