<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/9/5 0005
 * Time: 16:26
 */

namespace Library\Virtual\Controller;

use Library\Exception\WebException;
use Library\Validate;

/**
 * Class AbstractController
 * @package Library\Virtual\Controller
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

    /**
     * @throws WebException
     */
    public function validateRequest()
    {
        $this->request = Validate::checkRequest($this->request);
    }
}