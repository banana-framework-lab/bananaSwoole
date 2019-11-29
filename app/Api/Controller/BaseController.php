<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/9/5 0005
 * Time: 16:28
 */

namespace App\Api\Controller;


use App\Api\Object\SessionObject;
use App\Api\Service\ResCodeService;
use Exception;
use Library\Exception\WebException;
use Library\Helper\RequestHelper;
use Library\Helper\ResponseHelper;
use Library\Virtual\Controller\AbstractController;

class BaseController extends AbstractController
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
        $this->sessionId = RequestHelper::cookie('PHPSESSID');
        $this->sessionInfo = new SessionObject($this->sessionId);
        if (!in_array(strtolower(RequestHelper::server('request_uri')), [
            '/api/admin/login'
        ])) {
            //验证用户信息
            $this->verifyUserInfo();
        }
    }

    /**
     * 判断session中是否有用户数据
     * @throws Exception
     */
    public function verifyUserInfo()
    {
        if (!$this->sessionInfo->id) {
            throw new WebException('用户没有登陆', ResCodeService::$noLogin);
        }
    }

    /**
     * 判断用户是否有权限访问
     * @throws Exception
     */
    public function verifyUserPermission()
    {
        throw new WebException('用户没有权限访问', ResCodeService::$noAuth);
    }
}