<?php

namespace Library\Abstracts\Form;

use Library\Container;
use Library\Exception\LogicException;

/**
 * Class AbstractMiddleWare
 * @package Library\Abstracts\Middle
 */
abstract class AbstractForm
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
     * @var array 请求数据非严格字段
     */
    private $requestNoStrictField = [];

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
     * @return AbstractForm
     */
    public function setRequestAfter(array $requestAfter): AbstractForm
    {
        $this->requestAfter = $requestAfter;
        return $this;
    }

    /**
     * 设置输入参数的默认值
     * @param array $requestDefault
     * @return AbstractForm
     */
    public function setRequestDefault(array $requestDefault): AbstractForm
    {
        $this->requestDefault = $requestDefault;
        return $this;
    }

    /**
     * 设置输入参数的key
     * @param array $requestField
     * @return AbstractForm
     */
    public function setRequestField(array $requestField): AbstractForm
    {
        $this->requestField = $requestField;
        return $this;
    }

    /**
     * 设置非严格输入参数的key
     * @param array $requestField
     * @return AbstractForm
     */
    public function setNoStrictField(array $requestField): AbstractForm
    {
        $this->requestNoStrictField = $requestField;
        return $this;
    }

    /**
     * 设置输入参数的报错信息
     * @param array $requestErrMsg
     * @return AbstractForm
     */
    public function setRequestErrMsg(array $requestErrMsg): AbstractForm
    {
        $this->requestErrMsg = $requestErrMsg;
        return $this;
    }


    /**
     * 获取http传入的数据
     * @return array
     * @throws LogicException
     */
    public function getFormData(): array
    {
        $httpData = [];

        // 严格字段
        foreach ($this->requestField as $key => $fieldName) {
            if (isset($this->requestData[$fieldName]) && $this->requestData[$fieldName] !== '') {
                $httpData[$fieldName] = ($this->requestData[$fieldName]);
            } else {
                if (isset($this->requestDefault[$fieldName])) {
                    if(is_callable($this->requestDefault[$fieldName])){
                        $defaultData = ($this->requestDefault[$fieldName])();
                        if($defaultData !== ''){
                            $httpData[$fieldName] = $defaultData;
                        }else{
                            throw new LogicException(
                                isset($this->requestErrMsg[$fieldName]) ? "{$this->requestErrMsg[$fieldName]}不能为空" : "{$fieldName}不能为空.",
                                 Container::getConfig()->get('response.code.middleware_error', 10006),
                                null,
                                Container::getConfig()->get('response.status.http_fail', 10001)
                            );
                        }
                    }else{
                        $httpData[$fieldName] = ($this->requestDefault[$fieldName]);
                    }
                } else {
                    throw new LogicException(
                        isset($this->requestErrMsg[$fieldName]) ? "{$this->requestErrMsg[$fieldName]}不能为空" : "{$fieldName}不能为空!!",
                        Container::getConfig()->get('response.code.middleware_error', 10006),
                        null,
                        Container::getConfig()->get('response.status.http_fail', 10001)
                    );
                }
            }
            if (isset($httpData[$fieldName]) && isset($this->requestAfter[$fieldName])) {
                $httpData[$fieldName] = $this->requestAfter[$fieldName]($httpData);
            }
        }

        // 非严格字段
        foreach ($this->requestNoStrictField as $noStrictKey => $noStrictFieldName) {
            if (isset($this->requestData[$noStrictFieldName]) && $this->requestData[$noStrictFieldName] !== '') {
                $httpData[$noStrictFieldName] = ($this->requestData[$noStrictFieldName]);
            } else {
                if (isset($this->requestDefault[$noStrictFieldName])) {
                    $httpData[$noStrictFieldName] = ($this->requestDefault[$noStrictFieldName]);
                }
            }
            if (isset($httpData[$noStrictFieldName]) && isset($this->requestAfter[$noStrictFieldName])) {
                $httpData[$noStrictFieldName] = $this->requestAfter[$noStrictFieldName]($httpData);
            }
        }

        return $httpData;
    }

}