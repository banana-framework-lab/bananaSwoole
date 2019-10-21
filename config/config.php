<?php
define('APP_NAME', 'tanwange_chat_framework');//项目名称
define('RUNNING_LOG', '/tmp/swoole/web_socket_server.log'); // 系统运行的输出日志
define('DEBUG', true);
define('CURL_CERT_FILE_PATH', ROOT_DIR . '/cert/curl/cacert.pem'); //CURL证书配置
define('SIGN_KEY', '');
define('URL_SURFIX', 'html'); //URL后缀
define('MAX_DB_NUMBER', 1);
define('SERVER_ID', 3); // 服务器ID
define('MAX_TABLE_NUMBER', 16);
define('DB_LIST', [ //数据库配置
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
    ],
]);

define('MONGO_LIST', [ //数据库配置
    'server' => [
        'url' => 'mongodb://tanwange:tanwangemongo888@101.132.105.248:27017/tanwange'
    ],
    'local' => [
        'url' => 'mongodb://tanwange:tanwangemongo888@101.132.105.248:27017/tanwange'
    ],
]);

define('REDIS_LIST', [
    'local' => [
        'host' => '127.0.0.1',
        'port' => '6379',
        'auth' => '',
        'database' => '0'
    ],
    'server' => [
        'host' => '127.0.0.1',
        'port' => '6379',
        'auth' => '',
        'database' => '0'
    ],
]);

define('RABBITMQ_CONFIG', [
    'local' => [
        'host' => '101.132.105.248',
        'port' => 5672,
        'user' => 'admin',
        'password' => 'tanwan888',
        'vhost' => 'tanwange_chat'
    ],
    'server' => [
        'host' => '101.132.105.248',
        'port' => 5672,
        'user' => 'admin',
        'password' => 'tanwan888',
        'vhost' => 'tanwange_chat'
    ]
]);

define('WEB_SERVER_PORT', 9501);
define('WEB_SOCKET_SERVER_PORT', 9502);

