<?php

namespace App\Api\Model\DataBaseModel;

use App\Api\Middle\AdminMiddle;
use App\Api\Property\AdminProperty;
use Library\Response;
use Library\Object\BuilderObject;
use Library\Virtual\Model\DataBaseModel\AbstractCoroutineMySqlModel;
use Library\Virtual\Property\AbstractProperty;

/**
 * Created by PhpStorm.
 * User: ZhongHao-Zh
 * Date: 2019/10/26
 * Time: 20:18
 */
class AdminCoroutineModel extends AbstractCoroutineMySqlModel
{
    /**
     * AdminModel constructor.
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->table = 'sva_admin';
        parent::__construct($attributes);
    }

    /**
     * @param array $where 查询条件
     * @param array $orderBy 排序条件
     * @return BuilderObject 查询构造器对象
     */
    protected function getCondition($where, $orderBy = []): BuilderObject
    {
        $builder = $this->builder;
        if (isset($where['username'])) {
            $builder->where('username', '=', $where['username']);
        }
        if (isset($where['password'])) {
            $builder->where('password', '=', $where['password']);
        }
        return $builder;
    }

    /**
     * 登陆检验
     * @param string $username
     * @param string $password
     * @return AdminProperty|null
     * @throws \Exception
     */
    public function login(string $username, string $password)
    {
        $this->setListColumns(['id', 'username', 'nickname', 'name', 'avatar', 'role_id', 'create_time', 'update_time', 'last_login_time', 'status']);
        $result = $this->getFirst([
            'username' => $username,
            'password' => $password
        ]);
        if ($result) {
            $result['password'] = '*';
            return (new AdminProperty())->setProperty($result);
        } else {
            return null;
        }
    }

    /**
     * 测试慢查询
     */
    public function longCheck()
    {
        Response::dump($this->builder->count());
    }

}