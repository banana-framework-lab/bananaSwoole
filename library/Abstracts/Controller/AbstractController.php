<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/9/5 0005
 * Time: 16:26
 */

namespace Library\Abstracts\Controller;

/**
 * Class AbstractController
 * @package Library\Abstracts\Controller
 */
Abstract class AbstractController
{
    /**
     * request请求对象
     * @var array $request
     */
    protected $request;

    /**
     * AbstractController constructor.
     * @param $request
     */
    public function __construct($request)
    {
        $this->request = $request;
    }
}