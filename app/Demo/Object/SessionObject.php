<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/10/30
 * Time: 19:44
 */

namespace App\Demo\Object;


use App\Demp\Model\CacheModel\SessionModel;

class SessionObject
{
    public $id;

    public $username;

    public $nickname;

    public $name;

    public $avatar;

    public $roleId;

    public $permission;

    /**
     * MessageObject constructor.
     * @param string $sessionId
     */
    public function __construct(string $sessionId)
    {
        $sessionInfo = (new SessionModel())->getSessionInfo($sessionId);
        if ($sessionInfo) {
            $this->id = $sessionInfo['id'] ?? '';
            $this->username = $sessionInfo['username'] ?? '';
            $this->nickname = $sessionInfo['nickname'] ?? '';
            $this->name = $sessionInfo['name'] ?? '';
            $this->avatar = $sessionInfo['avatar'] ?? '';
            $this->roleId = $sessionInfo['roleId'] ?? '';
            $this->permission = $sessionInfo['permission'] ?? '';
        }
    }
}