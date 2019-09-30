<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/9/5 0005
 * Time: 16:26
 */

namespace App\Library\Virtual\Controller;



use App\Library\Entity\Response;

class AbstractController {

    /**
     * request请求对象
     * @var
     */
    protected $request;

    /**
     * Base constructor.
     * @param $request
     */
    public function __construct($request)
    {
        $this->request = $request;
    }

    /**
     * 自动匹配-用什么返回方式
     * @param $res
     * @param array $data
     * @return false|string
     */
    public function autoResponse($res, $data = [])
    {
        return $res ? $this->responseSuccess($data) : $this->responseFailed();
    }

    /**
     * 操作成功json返回
     * @param array $data
     * @return false|string
     */
    public function responseSuccess($data = [])
    {
        return Response::instance()->responseSuccess($data);
    }

    /**
     * 操作失败json返回
     * @param array $data
     * @return false|string
     */
    public function responseFailed($data = [])
    {
        return Response::instance()->responseFailed($data);
    }

    /**
     * 普通数据json返回
     * @param array $data
     * @return false|string
     */
    public function responseArray($data = [])
    {
        return Response::instance()->responseArray($data);
    }
}