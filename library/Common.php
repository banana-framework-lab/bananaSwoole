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
     * 已经加载过公共文件的项目名
     * @var array $loadIndex
     */
    private static $loadIndex = [];

    /**
     * 加载公共文件
     * @param string $projectName
     */
    public static function loadCommonFile(string $projectName = '')
    {
        if ($projectName == '') {
            include_once dirname(__FILE__) . "/Common/functions.php";
        } else {
            $filePath = dirname(__FILE__) . "/../app/{$projectName}/Common/functions.php";
            if (file_exists($filePath)) {
                include_once $filePath;
                self::$loadIndex[$projectName] = true;
            } else {
                self::$loadIndex[$projectName] = true;
            }
        }
    }

    /**
     * 自动根据路由加载文件
     * @param RouteObject $router
     */
    public static function autoLoadProjectCommonFile(RouteObject $router)
    {
        $project = $router->getProject();
        if (!isset(self::$loadIndex[$project])) {
            self::loadCommonFile($project);
        }
    }
}