<?php

namespace Library\Container\Instance;

/**
 * Created by PhpStorm.
 * User: ZhongHao-Zh
 * Date: 2019/10/20
 * Time: 16:55
 */

/**
 * Class Config
 * @package Library\Container
 */
class Config
{
    /**
     * @var array $configPool
     */
    private $configPool = [];

    /**
     * 初始化Config类
     */
    public function initConfig()
    {
        $handler = opendir(dirname(__FILE__) . '/../../../config');
        while (($fileName = readdir($handler)) !== false) {
            if ($fileName != "." && $fileName != ".." && $fileName != "example") {
                $fileIndex = (explode('.', $fileName))[0];
                if ($fileIndex != 'swoole') {
                    $fileData = include dirname(__FILE__) . '/../../../config/' . $fileName;
                    $this->configPool[$fileIndex] = $fileData;
                }
            }
        }
        closedir($handler);
    }

    /**
     * 初始化swooleConfig类
     */
    public function initSwooleConfig()
    {
        if (file_exists(dirname(__FILE__) . '/../../../config/swoole.php')) {
            $fileData = include dirname(__FILE__) . '/../../../config/swoole.php';
            $this->configPool['swoole'] = $fileData;
        } else {
            echo "无配置Swoole配置文件" . PHP_EOL;
            exit;
        }
    }

    /**
     * 获取配置
     * @param string $param
     * @param mixed $default
     * @return mixed
     */
    public function get(string $param, $default = "")
    {
        if ($param) {
            $paramArray = explode('.', $param);
            if (isset($this->configPool[$paramArray[0]])) {
                $returnData = $this->configPool[$paramArray[0]];
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

    /**
     * 返回所有配置数据
     * @return array
     */
    public function getAllConfig()
    {
        return $this->configPool;
    }

}