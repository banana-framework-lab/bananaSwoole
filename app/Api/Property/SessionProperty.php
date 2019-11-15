<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/11/15
 * Time: 20:06
 */

namespace App\Api\Property;

use Library\Virtual\Property\AbstractProperty;

class SessionProperty extends AbstractProperty
{
    public $id;

    public $username;

    public $nickname;

    public $name;

    public $password;

    public $avatar;

    public $roleId;

    public $createTime;

    public $updateTime;

    public $lastLoginTime;

    public $status;
}