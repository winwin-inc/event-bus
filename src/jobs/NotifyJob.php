<?php

namespace winwin\eventBus\jobs;

use Domnikl\Statsd;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use kuiper\di\annotation\Inject;
use kuiper\helper\Arrays;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use winwin\db\orm\Repository;
use winwin\eventBus\constants\EventStatus;
use winwin\eventBus\exceptions\NotifyException;
use winwin\eventBus\models\Event;
use winwin\eventBus\models\Subscriber;
use winwin\jobQueue\JobInterface;
use winwin\jobQueue\JobQueueInterface;

class NotifyJob implements JobInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

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
     * @Inject
     *
     * @var ClientInterface
     */
    private $httpClient;

    /**
     * @Inject
     *
     * @var Statsd\Client
     */
    private $statsdClient;

    private static $RETRY_INTERVALS = [15, 30, 300, 1800];

    /**
     * @param array $arguments
     */
    public function process(array $arguments)
    {
        /** @var Event $event */
        $event = $this->eventRepository->findOne(['event_id' => $arguments['event_id']]);
        if ($event->getStatus()->isDone()) {
            return;
        }

        if (empty($arguments['subscribers'])) {
            /** @var Subscriber[] $subscribers */
            $subscribers = $this->subscriberRepository->query(['topic' => $event->getTopic()]);
        } else {
            $subscribers = [];
            foreach ($arguments['subscribers'] as $notifyUrl) {
                $subscriber = new Subscriber();
                $subscriber->setTopic($event->getTopic())
                    ->setNotifyUrl($notifyUrl);
                $subscribers[] = $subscriber;
            }
        }

        $failed = $this->notifyAll($event, $subscribers);

        if (empty($failed)) {
            $event->setStatus(EventStatus::DONE());
        } else {
            if ($this->retry($arguments, $failed)) {
                $event->setStatus(EventStatus::RETRY());
            } else {
                $this->statsdClient->increment(sprintf('eventbus.notify.error.%s.%s', $event->getTopic(), $event->getEventName()));
                $event->setStatus(EventStatus::ERROR());
            }
        }
        $this->eventRepository->update($event);
    }

    /**
     * @param Event      $event
     * @param Subscriber $subscriber
     *
     * @throws NotifyException
     */
    private function notify(Event $event, Subscriber $subscriber)
    {
        $payload = [
            'create_time' => $event->getCreateTime()->format(DATE_ATOM),
            'event_id' => $event->getEventId(),
            'topic' => $event->getTopic(),
            'event_name' => $event->getEventName(),
            'payload' => $event->getPayload(),
        ];
        $this->logger->info('[NotifyJob] notify '.$subscriber->getNotifyUrl(), [
            'payload' => $payload,
        ]);
        try {
            $response = $this->httpClient->request('POST', $subscriber->getNotifyUrl(), [
                'headers' => [
                    'content-type' => 'application/json',
                ],
                'body' => json_encode($payload),
            ]);
            $ret = json_decode((string) $response->getBody(), true);
            if (!isset($ret['success']) || $ret['success'] !== true) {
                throw new NotifyException('Bad notification response: '.$response->getBody());
            }
        } catch (GuzzleException $e) {
            throw new NotifyException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param array        $arguments
     * @param Subscriber[] $failed
     *
     * @return bool
     */
    private function retry(array $arguments, array $failed)
    {
        $retryTimes = $arguments['retry_times'] ?? 0;
        $arguments['retry_times'] = $retryTimes + 1;
        $arguments['subscribers'] = Arrays::pull($failed, 'notifyUrl', Arrays::GETTER);
        if ($retryTimes >= count(self::$RETRY_INTERVALS)) {
            $this->logger->error('[NotifyJob] notify stopped', [
                'event_id' => $arguments['event_id'],
                'subscribers' => $arguments['subscribers'],
            ]);

            return false;
        } else {
            $this->jobQueue->put(__CLASS__, $arguments, self::$RETRY_INTERVALS[$retryTimes]);

            return true;
        }
    }

    /**
     * @param Event        $event
     * @param Subscriber[] $subscribers
     *
     * @return Subscriber[]
     */
    private function notifyAll($event, $subscribers): array
    {
        $failed = [];
        foreach ($subscribers as $subscriber) {
            $startTime = microtime(true);
            try {
                $this->notify($event, $subscriber);
                $this->statsdClient->timing(sprintf('eventbus.notify.success.%s.%s', $event->getTopic(), $event->getEventName()),
                    (int) (microtime(true) - $startTime) * 1000);
            } catch (NotifyException $e) {
                $this->statsdClient->timing(sprintf('eventbus.notify.fail.%s.%s', $event->getTopic(), $event->getEventName()),
                    (int) (microtime(true) - $startTime) * 1000);
                $this->logger->warning('[NotifyJob] notify failed', [
                    'event_id' => $event->getEventId(),
                    'notify_url' => $subscriber->getNotifyUrl(),
                    'error' => $e->getMessage(),
                ]);
                $failed[] = $subscriber;
            }
        }

        return $failed;
    }
}
