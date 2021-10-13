<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/9/5 0005
 * Time: 16:26
 */

namespace Library\Virtual\Controller;

use Library\Exception\LogicException;

/**
 * Class AbstractController
 * @package Library\Abstract\Controller
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