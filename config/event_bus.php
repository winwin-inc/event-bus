<?php

return [
    'beanstalk' => [
        'tube' => getenv('EVENTBUS_BEANSTALK_TUBE') ?: 'eventbus',
    ],
];
