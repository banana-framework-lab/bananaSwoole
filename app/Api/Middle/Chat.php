<?php

namespace App\Api\Middle;

use App\Library\Virtual\Middle\AbstractMiddleWare;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/6/14
 * Time: 14:21
 */
class Chat extends AbstractMiddleWare
{
    /**
     * ChatMiddle constructor.
     * @param $request
     */
    public function __construct($request)
    {
        parent::__construct($request);
    }

    /**
     * ChatController的messageKey的中间件
     */
    public function messageKey()
    {
        $this->setRequestField([
            'personal_uid',
            'opponent_uid',
            'platform_id',
            'platform_secret',
            'opponent_name',
            'personal_name',
        ])->setRequestErrMsg([
        ])->setRequestAfter([
        ])->setRequestDefault([
        ]);
    }

    /**
     * ChatController的history的中间件
     */
    public function history()
    {
        $this->setRequestField([
            'platform_id',
            'personal_uid',
            'opponent_uid',
            'max_id',
            'page',
            'limit'
        ])->setRequestErrMsg([
        ])->setRequestAfter([
        ])->setRequestDefault([
            'platform_id' => 13090,
            'max_id' => 0,
            'page' => 1,
            'limit' => 10
        ]);
    }

    /**
     * ChatController的online的中间件
     */
    public function online()
    {
        $this->setRequestField([
            'uid'
        ])->setRequestErrMsg([
        ])->setRequestAfter([
        ])->setRequestDefault([
        ]);
    }

    /**
     * ChatController的sessionList的中间件
     */
    public function sessionList()
    {
        $this->setRequestField([
            'platform_id',
            'uid',
            'max_id',
            'page',
            'limit'
        ])->setRequestErrMsg([
        ])->setRequestAfter([
        ])->setRequestDefault([
            'platform_id' => 13090,
            'max_id' => 0,
            'page' => 1,
            'limit' => 10
        ]);
    }

    /**
     * ChatController的getSessionInfo的中间件
     */
    public function getSessionInfo()
    {
        $this->setRequestField([
            'platform_id',
            'personal_uid',
            'opponent_uid'
        ])->setRequestErrMsg([
        ])->setRequestAfter([
        ])->setRequestDefault([
            'platform_id' => 13090
        ]);
    }
}