<?php

namespace App\Library\Virtual\Middle;


abstract class AbstractMiddleWare
{
    /**
     * @var array 请求数据
     */
    private $requestData = [];

    /**
     * @var array 请求数据修饰
     */
    private $requestAfter = [];

    /**
     * @var array 请求数据默认值
     */
    private $requestDefault = [];

    /**
     * @var array 请求数据必要字段
     */
    private $requestField = [];

    /**
     * @var array 请求数据错误信息
     */
    private $requestErrMsg = [];

    /**
     * AbstractMiddleWare constructor.
     * @param $request
     */
    public function __construct($request)
    {
        $this->requestData = $request;
    }

    /**
     * 设置输入参数的中间键加工函数
     * @param array $requestAfter
     * @return AbstractMiddleWare
     */
    public function setRequestAfter(array $requestAfter): AbstractMiddleWare
    {
        $this->requestAfter = $requestAfter;
        return $this;
    }

    /**
     * 设置输入参数的默认值
     * @param array $requestDefault
     * @return AbstractMiddleWare
     */
    public function setRequestDefault(array $requestDefault): AbstractMiddleWare
    {
        $this->requestDefault = $requestDefault;
        return $this;
    }

    /**
     * 设置输入参数的key
     * @param array $requestField
     * @return AbstractMiddleWare
     */
    public function setRequestField(array $requestField): AbstractMiddleWare
    {
        $this->requestField = $requestField;
        return $this;
    }

    /**
     * 设置输入参数的报错信息
     * @param array $requestErrMsg
     * @return AbstractMiddleWare
     */
    public function setRequestErrMsg(array $requestErrMsg): AbstractMiddleWare
    {
        $this->requestErrMsg = $requestErrMsg;
        return $this;
    }

    /**
     * 设置输入参数是否严格模式
     * @param bool $strict
     * @throws \Exception
     */
    public function setRequestStrict($strict = true)
    {
        $request_field = [];
        foreach ($this->requestField as $key => $field_name) {
            if (isset($this->requestData[$field_name]) && $this->requestData[$field_name] !== '') {
                $request_field[] = $field_name;
            } else {
                if (isset($this->requestDefault[$field_name])) {
                    $request_field[] = $field_name;
                } else {
                    if ($strict) {
                        throw new \Exception(isset($this->requestErrMsg[$field_name]) ? "{$this->requestErrMsg[$field_name]}不能为空" : "{$field_name}不能为空！");
                    }
                }
            }
            $this->requestField = $request_field;

        }
    }

    /**
     * 获取http传入的数据
     * @return array
     * @throws \Exception
     */
    public function takeMiddleData()
    {
        $httpData = [];
        foreach ($this->requestField as $key => $field_name) {
            if (isset($this->requestData[$field_name]) && $this->requestData[$field_name] !== '') {
                $httpData[$field_name] = ($this->requestData[$field_name]);
            } else {
                if (isset($this->requestDefault[$field_name])) {
                    $httpData[$field_name] = ($this->requestDefault[$field_name]);
                } else {
                    throw new \Exception(isset($this->requestErrMsg[$field_name]) ? "{$this->requestErrMsg[$field_name]}不能为空" : "{$field_name}不能为空!!");
                }
            }
            if (isset($httpData[$field_name]) && isset($this->requestAfter[$field_name])) {
                $httpData[$field_name] = $this->requestAfter[$field_name]($httpData);
            }
        }
        return $httpData;
    }

}