<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/11/1
 * Time: 17:35
 */
return [
    'local' => [
        'host' => '127.0.0.1',
        'port' => 5672,
        'user' => 'banana',
        'password' => '123456',
        'vhost' => 'chatvisory',
        'message_exchange' => 'websocket_message_exchange'
    ],
    'server' => [
        'host' => '127.0.0.1',
        'port' => 5672,
        'user' => 'banana',
        'password' => '123456',
        'vhost' => 'chatvisory',
        'message_exchange' => 'websocket_message_exchange'
    ],
    'supervisory' => [
        'host' => '127.0.0.1',
        'port' => 5672,
        'user' => 'banana',
        'password' => '123456',
        'vhost' => 'chatvisory',
        'message_exchange' => 'chatvisory_message_exchange'
    ]
];