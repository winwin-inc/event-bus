<?php

/**
 * NOTE: This class is auto generated by Tars Generator (https://github.com/wenbinye/tars-generator).
 *
 * Do not edit the class manually.
 * Tars Generator version: 1.0-SNAPSHOT
 */

namespace winwin\eventBus\client;

use wenbinye\tars\protocol\annotation\TarsClient;
use wenbinye\tars\protocol\annotation\TarsParameter;
use wenbinye\tars\protocol\annotation\TarsReturnType;

/**
 * @TarsClient(name="EventBusSubscriberObj")
 */
interface EventBusSubscriberServant
{
    /**
     * @TarsParameter(name = "notification", type = "Notification")
     * @TarsReturnType(type = "void")
     *
     * @param \winwin\eventBus\client\Notification $notification
     *
     * @return void
     */
    public function handle($notification);
}
