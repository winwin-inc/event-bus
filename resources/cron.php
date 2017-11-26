<?php

$schedule->exec(PHP_BINARY.' console event-bus:purge')
    ->runInBackground()
    ->at('01:33');
