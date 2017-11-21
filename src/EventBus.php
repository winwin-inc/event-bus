<?php

namespace winwin\eventBus;

use kuiper\di\annotation\Inject;
use Ramsey\Uuid\Uuid;
use Valitron\Validator;
use winwin\db\orm\Repository;
use winwin\eventBus\constants\EventStatus;
use winwin\eventBus\facade\EventBusInterface;
use winwin\eventBus\facade\exception\AlreadySubscribedException;
use winwin\eventBus\jobs\NotifyJob;
use winwin\eventBus\models\Event;
use winwin\eventBus\models\Subscriber;
use winwin\jobQueue\JobQueueInterface;
use winwin\support\exception\ValidationException;

class EventBus implements EventBusInterface
{
    /**
     * @Inject("eventBus.EventRepository")
     *
     * @var Repository
     */
    private $eventRepository;

    /**
     * @Inject("eventBus.SubscriberRepository")
     *
     * @var Repository
     */
    private $subscriberRepository;

    /**
     * @Inject("eventBus.JobQueue")
     *
     * @var JobQueueInterface
     */
    private $jobQueue;

    /**
     * 发布消息.
     *
     * @param string $topic     订阅主题
     * @param string $eventName 消息名称
     * @param array  $payload   消息体
     *
     * @return string 返回 event_id
     */
    public function publish($topic, $eventName, array $payload)
    {
        $payloadStr = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $validator = new Validator([
            'topic' => $topic,
            'event_name' => $eventName,
            'payload' => $payloadStr,
        ]);
        $validator->rule('required', ['topic', 'event_name', 'payload'])
            ->rule('lengthMax', ['topic', 'event_name'], 30)
            ->rule('lengthMax', ['payload'], 1000);
        if (!$validator->validate()) {
            throw new ValidationException($validator->errors());
        }

        $event = new Event();
        $event->setEventId(Uuid::uuid1())
            ->setTopic($topic)
            ->setEventName($eventName)
            ->setPayload($payload)
            ->setStatus(EventStatus::CREATE());
        $this->eventRepository->insert($event);
        $this->jobQueue->put(NotifyJob::class, [
            'event_id' => $event->getEventId(),
        ]);

        return $event->getEventId();
    }

    /**
     * 订阅消息.
     *
     * @param string $topic     订阅主题
     * @param string $notifyUrl 处理订阅消息回调地址
     *
     * @throws \InvalidArgumentException                                    如果 topic 不存在
     * @throws \winwin\eventBus\facade\exception\AlreadySubscribedException 如果重复订阅
     */
    public function subscribe($topic, $notifyUrl)
    {
        $validator = new Validator([
            'topic' => $topic,
            'notify_url' => $notifyUrl,
        ]);
        $validator->rule('required', ['topic', 'notify_url'])
            ->rule('lengthMax', ['topic'], 30)
            ->rule('lengthMax', ['notify_url'], 250);
        if (!$validator->validate()) {
            throw new ValidationException($validator->errors());
        }

        $subscriber = $this->subscriberRepository->findOne(['topic' => $topic, 'notify_url' => $notifyUrl]);
        if ($subscriber) {
            throw new AlreadySubscribedException("Topic '$topic' was already subscribed by '$notifyUrl'");
        }
        $subscriber = new Subscriber();
        $subscriber->setTopic($topic)
            ->setNotifyUrl($notifyUrl);
        $this->subscriberRepository->insert($subscriber);
    }

    /**
     * @param Repository $eventRepository
     */
    public function setEventRepository(Repository $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }

    /**
     * @param Repository $subscriberRepository
     */
    public function setSubscriberRepository(Repository $subscriberRepository)
    {
        $this->subscriberRepository = $subscriberRepository;
    }

    /**
     * @param JobQueueInterface $jobQueue
     */
    public function setJobQueue(JobQueueInterface $jobQueue)
    {
        $this->jobQueue = $jobQueue;
    }
}
