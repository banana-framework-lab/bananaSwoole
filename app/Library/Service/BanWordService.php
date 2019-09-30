<?php

namespace App\Library\Service;

/**
 * Created by PhpStorm.
 * User: zzh
 * Date: 2019/4/23
 * Time: 14:12
 */
class BanWord
{
    /**
     * 静态对象
     * @var null
     */
    protected static $instance = null;

    /**
     * 获取实例
     * @return null|static
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
     * 关键词屏蔽
     * @param string $content
     * @param boolean $reply
     * @return string
     * $reply = true return 替换后的$content
     * $reply = false return array 匹配的词
     */
    public function banWord($content, $reply = true)
    {
        if (!$content) {
            return "";
        }
        $words = file_get_contents("http://image.tanwan.com/huodong/banwords.txt");
        //关键词用|分隔开
        if ($reply) {
            $matched = preg_replace("/{$words}/i", '**', $content);
            return $matched;
        } else {
            return preg_match("/{$words}/i", $content, $matched);
        }

    }
}