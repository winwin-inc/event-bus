<?php

return [
    'providers' => [
        winwin\eventBus\EventBusServiceProvider::class,
    ],
    'rpc_server' => [
        'services' => [
            winwin\eventBus\facade\EventBusInterface::class,
        ],
    ],
];
