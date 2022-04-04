<?php

use Phalcon\Logger\Adapter\Stream;

return [
    'app' => [
        'name' => 'My phalcon app',
        'versiob' => '2.0'
    ],
    'db' => [
        'host'     => 'mysql-server',
        'username' => 'root',
        'password' => 'secret',
        'dbname'   => 'Store',
    ],
    'log' => [
        'login' => new Stream(APP_PATH . "/storage/logs/login.log"),
        'signup' => new Stream(APP_PATH . "/storage/logs/signup.log"),
    ]
];
