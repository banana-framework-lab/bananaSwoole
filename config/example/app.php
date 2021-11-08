<?php

return [
    // 是否自动重启
    'is_auto_reload' => true,
    // 是否测试模式(测试模式下会打印错误栈)
    'debug' => false,
    // 默认session可用的origin
    'access_control_allow_origin' => [
        'http://www.bananaswoole.com'
    ],
    'access_control_allow_methods' => [
        'GET', 'POST', 'DELETE', 'PUT', 'PATCH', 'OPTIONS'
    ],
    'access_control_allow_headers' => [
        'x-requested-with', 'User-Platform', 'Content-Type', 'X-Token'
    ]
];