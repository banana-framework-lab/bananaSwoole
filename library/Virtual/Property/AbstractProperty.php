<?php

namespace Library\Virtual\Property;

use stdClass;

/**
 * Class AbstractProperty
 * @package Library\Virtual\Property
 */
abstract class AbstractProperty
{
    /**
     * 设置属性
     * @param array $params
     * @return AbstractProperty
     * @throws \Exception
     */
    public function setProperty(array $params)
    {
        $needParams = get_object_vars($this);
        foreach ($needParams as $key => $value) {
            if (!isset($params[$key]) && $value === NULL) {
                throw new \Exception("{$key}不能为空");
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
        $ref = null;
        try {
            $ref = new \ReflectionClass(static::class);
        } catch (\ReflectionException $e) {
        }
        $ownProps = array_filter($ref->getProperties(), function ($property) {
            return $property->class == static::class;
        });
        /**
         * @var \ReflectionProperty $value
         */
        foreach ($ownProps as $key => $value) {
            $result[$value->getName()] = $this->{$value->getName()};
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
        $ref = null;
        try {
            $ref = new \ReflectionClass(static::class);
        } catch (\ReflectionException $e) {
        }
        $ownProps = array_filter($ref->getProperties(), function ($property) {
            return $property->class == static::class;
        });
        /**
         * @var \ReflectionProperty $value
         */
        foreach ($ownProps as $key => $value) {
            $collect->{$value->getName()} = $this->{$value->getName()};
        }
        return $collect;
    }
}