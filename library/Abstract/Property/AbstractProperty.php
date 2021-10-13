<?php

namespace Library\Virtual\Property;

use Exception;
use stdClass;

/**
 * Class AbstractProperty
 * @package Library\Abstract\Property
 */
abstract class AbstractProperty
{
    /**
     * 设置属性
     * 可以默认写法
     * public function setProperty(array $params)
     * {
     *    return $this->__setProperty($params);
     * }
     * @param array $params
     * @return $this
     * @throws Exception
     */
    abstract public function setProperty(array $params);

    /**
     * 设置属性
     * @param array $params
     * @return $this
     * @throws Exception
     */
    protected function __setProperty(array $params)
    {
        $needParams = get_object_vars($this);
        foreach ($needParams as $key => $value) {
            if (!isset($params[$key]) && $value === NULL) {
                throw new Exception("{$key}不能为空");
            } else {
                $this->$key = $params[$key] ?? $this->$key;
            }
        }
        return $this;
    }

    /**
     * 生成数组
     * @return array
     */
    public function toArray(): array
    {
        $result = [];
        $needParams = get_object_vars($this);
        foreach ($needParams as $key => $value) {
            $result[$key] = $value;
        }
        return $result;
    }

    /**
     * 生成集合
     * @return stdClass
     */
    public function toObject(): stdClass
    {
        $collect = new stdClass();
        $needParams = get_object_vars($this);
        foreach ($needParams as $key => $value) {
            $collect->{$key} = $value;
        }
        return $collect;
    }
}