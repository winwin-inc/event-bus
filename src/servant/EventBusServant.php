<?php

/**
 * NOTE: This class is auto generated by Tars Generator (https://github.com/wenbinye/tars-generator).
 *
 * Do not edit the class manually.
 * Tars Generator version: 1.0-SNAPSHOT
 */

namespace winwin\eventBus\servant;

use wenbinye\tars\protocol\annotation\TarsParameter;
use wenbinye\tars\protocol\annotation\TarsReturnType;
use wenbinye\tars\protocol\annotation\TarsServant;

/**
 * @TarsServant(name="EventBusObj")
 */
interface EventBusServant
{
    /**
     * @TarsParameter(name = "topic", type = "string")
     * @TarsParameter(name = "event", type = "string")
     * @TarsParameter(name = "payload", type = "string")
     * @TarsReturnType(type = "string")
     *
     * @param string $topic
     * @param string $event
     * @param string $payload
     *
     * @return string
     */
    public function publish($topic, $event, $payload);

    /**
     * @TarsParameter(name = "topic", type = "string")
     * @TarsParameter(name = "event", type = "string")
     * @TarsParameter(name = "payload", type = "string")
     * @TarsReturnType(type = "bool")
     *
     * @param string $topic
     * @param string $event
     * @param string $payload
     *
     * @return bool
     */
    public function publishNow($topic, $event, $payload);

    /**
     * @TarsParameter(name = "topic", type = "string")
     * @TarsParameter(name = "handler", type = "string")
     * @TarsReturnType(type = "void")
     *
     * @param string $topic
     * @param string $handler
     *
     * @return void
     */
    public function subscribe($topic, $handler);

    /**
     * @TarsParameter(name = "topic", type = "string")
     * @TarsParameter(name = "handler", type = "string")
     * @TarsReturnType(type = "bool")
     *
     * @param string $topic
     * @param string $handler
     *
     * @return bool
     */
    public function unsubscribe($topic, $handler);

    /**
     * @TarsParameter(name = "keepDays", type = "int")
     * @TarsReturnType(type = "int")
     *
     * @param int $keepDays
     *
     * @return int
     */
    public function purge($keepDays);
}
