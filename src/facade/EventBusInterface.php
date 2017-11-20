<?php

namespace winwin\eventBus\facade;

interface EventBusInterface
{
    /**
     * 发布消息.
     *
     * @param string $topic     订阅主题
     * @param string $eventName 消息名称
     * @param array  $payload   消息体
     *
     * @return string 返回 event_id
     */
    public function publish($topic, $eventName, array $payload);

    /**
     * 订阅消息.
     *
     * @param string $topic     订阅主题
     * @param string $notifyUrl 处理订阅消息回调地址
     *
     * @throws \InvalidArgumentException                                    如果 topic 不存在
     * @throws \winwin\eventBus\facade\exception\AlreadySubscribedException 如果重复订阅
     */
    public function subscribe($topic, $notifyUrl);
}
