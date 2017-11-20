<?php

return [
    'file' => getenv('LOGGER_FILE') ?: 'php://stderr',
    'level' => getenv('LOGGER_LEVEL') ?: 'debug',
];
