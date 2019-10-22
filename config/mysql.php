<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/10/22
 * Time: 17:35
 */
return [
    'server' => [
        'driver' => 'mysql',
        'host' => '127.0.0.1',
        'database' => 'tanwange_socket',
        'username' => 'root',
        'password' => '123456',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_general_ci',
        'prefix' => '',
    ],
    'local' => [
        'driver' => 'mysql',
        'host' => '127.0.0.1',
        'database' => 'tanwange_socket',
        'username' => 'root',
        'password' => '123456',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_general_ci',
        'prefix' => 'twg_',
    ]
];