<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/9/5 0005
 * Time: 17:05
 */

return [
    '/chat/history' => '\App\Api\Controller\Chat@history',
    '/chat/session' => '\App\Api\Controller\Chat@sessionList',
    '/chat/session/title' => '\App\Api\Controller\Chat@sessionTitle',
    '/chat/online' => '\App\Api\Controller\Chat@online',
    '/chat/message/key' => '\App\Api\Controller\Chat@messageKey'
];