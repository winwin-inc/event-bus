<?php

namespace winwin\eventBus\constants;

use kuiper\helper\Enum;

class EventStatus extends Enum
{
    const CREATE = 0;
    const DONE = 1;
    const ERROR = 2;
    const RETRY = 3;

    public static function fromValue($value, $default = null)
    {
        return parent::fromValue($value, $default ?: self::CREATE());
    }

    public function isDone()
    {
        return in_array($this->value, [self::DONE, self::ERROR]);
    }
}
