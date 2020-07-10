<?php

namespace winwin\eventBus\application;

use winwin\eventBus\domain\Event;
use winwin\eventBus\domain\Subscriber;

interface EventBusNotifyService
{
    /**
     * 发送消息.
     *
     * @param Subscriber[] $subscribers
     *
     * @return Subscriber[]
     */
    public function send(Event $event, array $subscribers): array;
}
