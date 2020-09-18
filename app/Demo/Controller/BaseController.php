<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/9/5 0005
 * Time: 16:28
 */
namespace App\Demo\Controller;

use App\Demo\Object\SessionObject;
use Exception;
use Library\Request;
use Library\Virtual\Controller\AbstractController;

Abstract class BaseController extends AbstractController
{
    /**
     * @var SessionObject $sessionInfo
     */
    public $sessionInfo;

    /**
     * @var string $sessionId
     */
    public $sessionId;

    /**
     * BaseController constructor.
     * @param $request
     * @throws Exception
     */
    public function __construct($request)
    {
        parent::__construct($request);
        $this->sessionId = Request::cookie('PHPSESSID');
        $this->sessionInfo = new SessionObject($this->sessionId);
    }
}