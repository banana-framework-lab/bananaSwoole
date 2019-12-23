<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/12/23
 * Time: 15:40
 */

namespace Library;

use Closure;
use Library\Exception\WebException;

/**
 * Class Validate
 * @package Library
 */
class Validate
{
    private static $expandFunctions = [];


    public static function initValidate()
    {
        self::$expandFunctions['trim'] = function ($data) {
            return trim($data);
        };
        self::$expandFunctions['strip_tags'] = function ($data) {
            return strip_tags($data);
        };
        self::$expandFunctions['htmlspecialchars'] = function ($data) {
            return htmlspecialchars($data);
        };
        self::$expandFunctions['addslashes'] = function ($data) {
            return addslashes($data);
        };
    }

    /**
     * 检查数据
     * @param $data
     * @param array $checkTypeList
     * @return mixed
     * @throws WebException
     */
    public static function checkRequest($data, array $checkTypeList = ['trim', 'strip_tags', 'htmlspecialchars', 'addslashes'])
    {
        if (is_array($data)) {
            foreach ($data as $key => $v) {
                $data[$key] = self::checkRequest($v, $checkTypeList);
            }
        } else {
            if (!is_bool($data)) {
                foreach ($checkTypeList as $checkTypeKey => $checkType) {
                    if (array_key_exists($checkType, self::$expandFunctions)) {
                        $function = self::$expandFunctions[$checkType];
                        $data = $function($data);
                    }
                }
            }
        }

        return $data;
    }

    /**
     * @param string $functionName
     * @param Closure $function
     */
    public static function addExpandFunction(string $functionName, Closure $function)
    {
        self::$expandFunctions[$functionName] = $function;
    }
}