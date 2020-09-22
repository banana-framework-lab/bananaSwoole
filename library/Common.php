<?php

namespace Library;

/**
 * Created by PhpStorm.
 * User: ZhongHao-Zh
 * Date: 2019/10/20
 * Time: 16:55
 */

use Library\Object\RouteObject;

/**
 * Class Config
 * @package Library
 */
class Common
{
    /**
     * 加载公共文件
     * @param string $projectName
     */
    public static function loadCommonFile(string $projectName = '')
    {
        if ($projectName == '') {
            include_once dirname(__FILE__) . "/Common/functions.php";
        } else {
            include_once dirname(__FILE__) . "/Common/functions.php";
            $filePath = dirname(__FILE__) . "/../app/{$projectName}/Common/functions.php";
            if (file_exists($filePath)) {
                include_once $filePath;
            }
        }
    }
}