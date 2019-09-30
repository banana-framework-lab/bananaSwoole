<?php
/**
 * websocket请求参数
 * User: zzh
 * Date: 2019/08/22
 */

namespace App\Server\Data;

use Swoole\Http\Request;

class OpenRequest
{
    /**
     * 平台ID
     * 实际上用来扩展的，可以忽略,现在是13090是贪玩阁的代号
     *
     * @var int
     */
    public $platformId = 13090;

    /**
     * socket客户端的唯一用户Id
     *
     * @var mixed $socketClientId
     */
    public $uuid;

    /**
     * socket客户端类型
     *
     * @var integer
     */
    public $type;

    /**
     * sign 签名。验签需要用到
     *
     * @var string
     */
    public $sign;

    /**
     * Http请求相关的头信息
     *
     * @var array
     */
    protected $header;

    /**
     * 秘钥
     */
    const SIGN_KEY = 'jubaofangbananaandwzwsocketframework';

    /**
 * 初始化参数对象
 *
 * @param array $property
 */
    public function __construct($property = [])
    {
        foreach ($property as $property_name => $property_value) {
            if (property_exists($this, $property_name)) {
                $this->$property_name = $property_value;
            }
        }
    }


    /**
     * 过滤黑名单
     *
     * @param Request $req
     */
    public function filterBlacklist(Request $req)
    {
        //用于过滤ip黑名单，应该重写
        var_dump('pass blackList');
    }

    /**
     * 获取真实IP
     *
     * @param int $type 0=字符串 1=整型ip
     * @param bool $client
     * @return mixed
     */
    public function get_client_ip($type = 0, $client = true)
    {
        $type = $type ? 1 : 0;
        static $ip = NULL;
        if ($ip !== NULL) return $ip[$type];
        if ($client) {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                $pos = array_search('unknown', $arr);
                if (false !== $pos) unset($arr[$pos]);
                $ip = trim($arr[0]);
            } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            } elseif (isset($_SERVER['REMOTE_ADDR'])) {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        // 防止IP伪造
        $long = sprintf("%u", ip2long($ip));
        $ip = $long ? array($ip, $long) : array('0.0.0.0', 0);
        return $ip[$type];
    }


    /**
     * 签名验证
     *
     * @param array $request 请求参数
     * @throws \Exception
     */
    public function verifySign(array $request)
    {
        var_dump('pass_open_sign');
        $sign = $request['sign'];
        unset($request['sign']);
        ksort($request);
        $mySign = strtoupper(md5(http_build_query($request) . self::SIGN_KEY));
        if ($mySign !== $sign) {
            throw new \Exception('错误签名');
        }
    }

    /**
     * 必要参数验证
     * @throws \Exception
     */
    public function verifyNecessary()
    {
        var_dump('open_necessary');
        // 必要参数验证
        $needParam = ['platformId', 'uuid', 'type', 'sign'];
        foreach ($needParam as $item) {
            if (empty($this->$item)) {
                throw new \Exception("缺少参数{$item}");
            }
        }
    }

    /**
     * 设置头部参数
     * @param $header
     */
    public function setRequestHeader($header)
    {
        $this->header = $header;
    }

    /**
     * 获取头部参数
     * @return array
     */
    public function getRequestHeader()
    {
        return $this->header;
    }

}