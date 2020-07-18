<?php

use function kuiper\helper\env;

return [
    'application' => [
        'database' => [
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', 3306),
            'name' => env('DB_NAME'),
            'user' => env('DB_USER'),
            'password' => env('DB_PASS'),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'table-prefix' => env('DB_TABLE_PREFIX', 'eventbus_'),
            'logging' => 'true' === env('DB_LOGGING'),
        ],
        'beanstalk' => [
            'host' => env('BEANSTALK_HOST', 'localhost'),
            'tube' => env('BEANSTALK_TUBE', 'event-bus'),
        ],
        'statsd' => [
            'host' => env('STATSD_HOST', 'localhost'),
        ],
        'job-processor' => [
            'enabled' => 'true' === env('JOB_QUEUE_ENABLED'),
        ],
        'logging' => [
            'level' => [
                # 'wenbinye\\tars' => 'debug',
            ],
        ],
    ],
];
