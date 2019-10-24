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
    private static $configPool = [];

    /**
     * 初始化Config类
     */
    public static function instanceStart()
    {
        $handler = opendir(dirname(__FILE__) . '/../config');
        while (($fileName = readdir($handler)) !== false) {
            if ($fileName != "." && $fileName != "..") {
                $fileIndex = (explode('.', $fileName))[0];
                if ($fileIndex != 'swoole') {
                    $fileData = include dirname(__FILE__) . '/../config/' . $fileName;
                    static::$configPool[$fileIndex] = $fileData;
                }
            }
        }
        closedir($handler);
    }

    /**
     * 初始化swooleConfig类
     */
    public static function instanceSwooleStart()
    {
        if (file_exists(dirname(__FILE__) . '/../config/swoole.php')) {
            $fileData = include dirname(__FILE__) . '/../config/swoole.php';
            static::$configPool['swoole'] = $fileData;
        } else {
            static::$configPool['swoole'] = [
                'web' => [
                    'port' => 9501,
                    'worker_num' => 1
                ],
                'socket' => [
                    'port' => 9502,
                    'worker_num' => 1
                ]
            ];
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
            if (isset(static::$configPool[$paramArray[0]])) {
                $returnData = static::$configPool[$paramArray[0]];
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