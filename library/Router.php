<?php
namespace Library;
/**
 * Created by PhpStorm.
 * User: ZhongHao-Zh
 * Date: 2019/10/20
 * Time: 16:55
 */
class Router
{
    /**
     * @var Router $instance
     */
    public static $instance = null;

    /**
     * @var array $routePool
     */
    public static $routePool = [];

    /**
     * 初始化Router类
     */
    public static function instanceStart()
    {
        if (!static::$instance) {
            $handler = opendir(dirname(__FILE__) . '../route');
            //务必使用!==，防止目录下出现类似文件名“0”等情况
            while (($fileName = readdir($handler)) !== false) {

                if ($fileName != "." && $fileName != "..") {
                    self::$routePool[] += require dirname(__FILE__) . '../route/' . $fileName;;
                }
            }
            closedir($handler);
        }
    }

}