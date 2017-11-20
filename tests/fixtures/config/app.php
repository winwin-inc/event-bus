<?php

defined('APP_PATH') or define('APP_PATH', realpath(__DIR__.'/../../..'));

return [
    'providers' => [
        kuiper\boot\providers\MonologProvider::class,
        kuiper\boot\providers\ConsoleApplicationProvider::class,
        winwin\providers\DbConnectionProvider::class,
        winwin\eventBus\EventBusServiceProvider::class,
    ],

    'redis' => [
        'parameters' => [
            'host' => getenv('REDIS_HOST') ?: 'localhost',
            'port' => getenv('REDIS_PORT') ?: 6379,
            'password' => getenv('REDIS_PASSWORD') ?: null,
            'database' => getenv('REDIS_DATABASE') ?: 15,
            'persistent' => true,
        ],
    ],

    'cache' => [
        'prefix' => getenv('APP_CACHE_PREFIX'),
        'lifetime' => 10,
    ],

    'beanstalk' => [
        'host' => getenv('BEANSTALK_HOST') ?: 'localhost',
        'port' => getenv('BEANSTALK_PORT') ?: 11300,
        'tube' => getenv('BEANSTALK_TUBE') ?: 'qrpay',
    ],

    'database' => [
        'host' => getenv('DB_HOST') ?: '127.0.0.1',
        'port' => getenv('DB_PORT') ?: 3306,
        'user' => getenv('DB_USER'),
        'password' => getenv('DB_PASS') ?: '',
        'dbname' => getenv('DB_NAME'),
        'charset' => getenv('DB_CHARSET') ?: 'utf8',
        'logging' => getenv('DB_LOGGING') == 'true',
    ],

    'http_client' => [
        'connect_timeout' => getenv('HTTP_CONNECT_TIMEOUT') ?: 5,
        'timeout' => getenv('HTTP_TIMEOUT') ?: 20,
    ],

    'dev_mode' => getenv('APP_DEV_MODE') == 'true',
    'base_path' => getenv('APP_BASE_PATH') ?: APP_PATH,
    'runtime_path' => '{app.base_path}/runtime',
];
