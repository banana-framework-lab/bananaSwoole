<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/9/5 0005
 * Time: 17:42
 */

namespace Library;

class AutoLoad
{
    public static function autoload($class)
    {
        // 如果存在的话，加载composer的autoload
        if (file_exists(__DIR__. '/../../vendor/autoload.php')) {
            include_once __DIR__. '/../vendor/autoload.php';
            return;
        }

        if (strpos($class, '\\') !== false) {
            $class = explode('\\', $class)[3];
        }

        // 加载项目的控制器类
        $classFile = __DIR__ . '/../Controller/' . $class . '.php';
        if (is_file($classFile) && !class_exists($class)) {
            include_once $classFile;
        }

        // 加载项目的逻辑类
        $classFile = __DIR__ . '/../Logic/' . $class . '.php';
        if (is_file($classFile) && !class_exists($class)) {
            include_once $classFile;
        }
    }
}