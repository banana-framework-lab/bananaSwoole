<?php

namespace App\Library\Entity;

class Response
{
    /**
     * @var int
     */
    public $successCode = 1;

    /**
     * @var
     */
    public $failCode = 0;
    /**
     * 静态对象
     * @var null
     */
    protected static $instance = null;

    /**
     * 获取实例
     * @return null|static
     */
    public static function instance()
    {
        if (empty(static::$instance)) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    public function responseSuccess($data = [])
    {
        //默认返回成功的数据
        $res_data = [
            'code' => 0,
            'data' => [],
            'msg' => '操作成功',
        ];

        foreach ($data as $key => $value) {
            if (array_key_exists($key, $res_data)) {
                $res_data[$key] = $value;
            }
        }

        return json_encode($res_data, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    }

    public function responseFailed($data = [])
    {
        //默认返回失败的数据
        $res_data = [
            'code' => 40000,
            'data' => [],
            'msg' => '操作失败',
        ];

        foreach ($data as $key => $value) {
            if (array_key_exists($key, $res_data)) {
                $res_data[$key] = $value;
            }
        }

        return json_encode($res_data, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    }

    public function responseArray($data = [])
    {
        //返回数据json
        $res_data = [
            'code' => 0,
            'data' => $data,
            'msg' => '请求成功',
        ];

        return json_encode($res_data, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    }
}