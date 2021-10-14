<?php
/**
 * Created by PhpStorm.
 * User: zzh
 * Date: 2019/4/23
 * Time: 14:12
 */

namespace Library\App\Service;


class PinYinService
{
    /**
     * 静态对象
     * @var PinYinService
     */
    protected static $instance = null;

    /**
     * 获取实例
     * @return PinYinService|static
     */
    public static function instance()
    {
        if (empty(static::$instance)) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    /**
     * 根据字符获取其首字母
     * @param $str
     * @return string
     */
    private function getFirstCharter($str)
    {
        if (empty($str)) {
            return '';
        }
        $fchar = ord($str{0});
        if ($fchar >= ord('A') && $fchar <= ord('z')) return strtoupper($str{0});
        $s1 = mb_convert_encoding($str, 'GBK', 'UTF-8');
        $s2 = mb_convert_encoding($s1, 'UTF-8', 'GBK');
        //保证字符串是utf-8编码
        $s = $s2 == $str ? $s1 : $str;
        //根据gb2312汉子排序得出的算法
        $asc = ord($s{0}) * 256 + ord($s{1}) - 65536;
        if ($asc >= -20319 && $asc <= -20284) return 'a';
        if ($asc >= -20283 && $asc <= -19776) return 'b';
        if ($asc >= -19775 && $asc <= -19219) return 'c';
        if ($asc >= -19218 && $asc <= -18711) return 'd';
        if ($asc >= -18710 && $asc <= -18527) return 'e';
        if ($asc >= -18526 && $asc <= -18240) return 'f';
        if ($asc >= -18239 && $asc <= -17923) return 'g';
        if ($asc >= -17922 && $asc <= -17418) return 'h';
        if ($asc >= -17417 && $asc <= -16475) return 'j';
        if ($asc >= -16474 && $asc <= -16213) return 'k';
        if ($asc >= -16212 && $asc <= -15641) return 'l';
        if ($asc >= -15640 && $asc <= -15166) return 'm';
        if ($asc >= -15165 && $asc <= -14923) return 'n';
        if ($asc >= -14922 && $asc <= -14915) return 'o';
        if ($asc >= -14914 && $asc <= -14631) return 'p';
        if ($asc >= -14630 && $asc <= -14150) return 'q';
        if ($asc >= -14149 && $asc <= -14091) return 'r';
        if ($asc >= -14090 && $asc <= -13319) return 's';
        if ($asc >= -13318 && $asc <= -12839) return 't';
        if ($asc >= -12838 && $asc <= -12557) return 'w';
        if ($asc >= -12556 && $asc <= -11848) return 'x';
        if ($asc >= -11847 && $asc <= -11056) return 'y';
        if ($asc >= -11055 && $asc <= -10247) return 'z';
        return '';
    }

    /**
     * 根据字符串获取其首字母
     * @param $zh
     * @return string
     */
    public function getStrFirstPY($zh)
    {
        $ret = "";
        $s1 = mb_convert_encoding($zh, 'GBK', 'UTF-8');
        $s2 = mb_convert_encoding($s1, 'UTF-8', 'GBK');
        if ($s2 == $zh) {
            $zh = $s1;
        }
        for ($i = 0; $i < strlen($zh); $i++) {
            $s1 = substr($zh, $i, 1);
            $p = ord($s1);
            if ($p > 160) {
                $s2 = substr($zh, $i++, 2);
                $ret .= $this->getFirstCharter($s2);
            } else {
                $ret .= $s1;
            }
        }
        return $ret;
    }

}