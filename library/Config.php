<?php

namespace Library;
/**
 * Created by PhpStorm.
 * User: ZhongHao-Zh
 * Date: 2019/10/20
 * Time: 16:55
 */
class Config
{
    /**
     * @var array $configPool
     */
    public static $configPool = [];

    /**
     * 初始化Router类
     */
    public static function instanceStart()
    {
        if (!self::$configPool) {
            $handler = opendir(dirname(__FILE__) . '/../config');
            while (($fileName = readdir($handler)) !== false) {
                if ($fileName != "." && $fileName != "..") {
                    $fileIndex = (explode('.', $fileName))[0];
                    self::$configPool[$fileIndex] = require dirname(__FILE__) . '/../config/' . $fileName;
                }
            }
            closedir($handler);
        }
    }

    /**
     * 获取配置
     * @param string $param
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $param, $default = "")
    {
        if ($param) {
            $paramArray = explode('.', $param);
            if (isset(self::$configPool[$paramArray[0]])) {
                $returnData = self::$configPool[$paramArray[0]];
            } else {
                return $default;
            }
            array_shift($paramArray);
            foreach ($paramArray as $key => $value) {
                if (isset($returnData[$value])) {
                    $returnData = $returnData[$value];
                } else {
                    return $default;
                }
            }
            return $returnData;
        } else {
            return $default;
        }
    }

}