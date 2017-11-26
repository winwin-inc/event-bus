<?php

return [
    'commands' => [
        winwin\eventBus\commands\PurgeCommand::class,
    ],
    'rpc_server' => [
        'services' => [
            winwin\eventBus\facade\EventBusInterface::class,
        ],
    ],
];
