<?php

return [
    'application' => [
        'viewsDir'       => __DIR__ . '/views/',
        'logsDir'        => __DIR__ . '/logs/',
        'cacheDir'       => __DIR__ . '/cache/',
    ],

    'redis' => [
        'host'        => 'localhost',
        'port'        => 6379,
        'database'    => 0,
        'prefix'      => 'soli:container:',
    ],

    'db' => [
        'host'        => '192.168.56.101',
        'port'        => '3306',
        'username'    => 'root',
        'password'    => 'root',
        'dbname'      => 'test',
        'charset'     => 'utf8',
    ],

    'providers' => [
        '\Soli\Providers\RedisServiceProvider',
        '\Soli\Providers\TestServiceProvider',
    ],
];
