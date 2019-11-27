<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/6/13
 * Time: 10:11
 */

namespace Library\App\Service;

use SimpleXMLElement;


class XmlService
{
    /**
     * 静态对象
     * @var XmlService $instance
     */
    protected static $instance = null;

    /**
     * 获取实例
     * @return XmlService
     */
    public static function instance()
    {
        if (empty(static::$instance)) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    /**
     * XML转为数组
     * @param string $xml XML字符串
     * @return array
     */
    public function xmlToArray($xml)
    {
        return json_decode(
            json_encode(
                (array)simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)
            ), true
        );
    }

    /**
     * 数组转为XML
     * @param  SimpleXMLElement $xml XML对象
     * @param  mixed $data 数据
     * @param  string $item 数字索引时的节点名称
     * @return string
     */
    public function arrayToXml($xml, $data, $item = 'item')
    {
        foreach ($data as $key => $value) {
            //指定默认的数字key
            is_numeric($key) && $key = $item;
            //添加子元素
            if (is_array($value) || is_object($value)) {
                $child = $xml->addChild($key);
                $this->arrayToXml($child, $value, $item);
            } else {
                if (is_numeric($value)) {
                    $child = $xml->addChild($key, $value);
                } else {
                    $child = $xml->addChild($key);
                    $node = dom_import_simplexml($child);
                    $cdata = $node->ownerDocument->createCDATASection($value);
                    $node->appendChild($cdata);
                }
            }
        }
        return $xml->asXML();
    }
}