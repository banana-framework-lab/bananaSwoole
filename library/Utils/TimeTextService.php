<?php
/**
 * 时间显示管理服务
 * User: zzh
 * Date: 2018/5/18
 * Time: 14:29
 */

namespace Library\App\Service;

class TimeTextService
{
    /**
     * 静态对象
     * @var null
     */
    protected static $instance = null;

    /**
     * http访问时间戳
     * @var $httpRequestTime string
     */
    protected static $httpRequestTime;

    /**
     * 获取实例
     * @return null | static
     */
    public static function instance()
    {
        if (empty(static::$instance)) {
            static::$httpRequestTime = $_SERVER['REQUEST_TIME'];
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
     * 时间文本配置
     * @var array
     */
    protected $timeTextTree = [
        3600 => [
            'text' => '%s分钟前',
            'time' => 60
        ],
        86400 => [
            'text' => '%s小时前',
            'time' => 3600
        ],
        604800 => [
            'text' => '%s天前',
            'time' => 86400
        ]
    ];

    /**
     * 获取时间文本
     * @param $time
     * @return string
     */
    public function getTimeText($time)
    {
        $dirTime = static::$httpRequestTime - $time;
        foreach ($this->timeTextTree as $key => $value) {
            if ((int)$dirTime < (int)$key) {
                $timeNumber = (int)($dirTime / $value['time']);
                if ($timeNumber <= 0) {
                    return '刚刚';
                }
                return sprintf($value['text'], $timeNumber);
            }
        }
        return date('Y年m月d日 H:i:s', $time);
    }

}