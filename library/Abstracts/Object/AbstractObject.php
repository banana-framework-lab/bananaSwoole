<?php
/**
 * 参数对象抽象类
 * User: dhq
 * Date: 2019/3/07
 * Time: 14:11
 */

namespace Library\Abstracts\Object;
use ArrayAccess;

class AbstractObject
{
    /**
     * 参数处理方法的后缀
     */
    const METHOD_SUFFIX = 'Format';

    /**
     * 钩子方法名，子类存在该方法时自动被调用
     */
    const METHOD_HOOK = 'assignHook';

    /**
     * 构造方法可以初始化对象属性值
     *
     * @param object|array|ArrayAccess $property
     */
    public function __construct($property = [])
    {
        $property = $property ?: [];
        // 设置属性值
        $class_properties = array_keys(get_object_vars($this));
        foreach ($property as $property_name => $property_value) {
            if (in_array($property_name, $class_properties)) {
                if ($property_value || (is_numeric($property_value) || is_bool($property_value))) {
                    $this->$property_name = $property_value;
                }
            }

            $method_format = camelize($property_name) . self::METHOD_SUFFIX;
            if (method_exists($this, $method_format)) {
                $this->$method_format();
            }
        }
        // 钩子方法
        $hook = self::METHOD_HOOK;
        if (method_exists($this, $hook)) $this->$hook();
    }

    /**
     * 将对象的属性转换成数组
     *
     * @return array
     */
    public function toArray()
    {
        return get_object_vars($this);
    }

    /**
     * 获取属性值
     * @return array
     */
    public function getAttrs(){
        $data = $this->toArray();
        return array_filter($data);
    }
}
