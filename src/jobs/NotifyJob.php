<?php

namespace winwin\eventBus\jobs;

use Domnikl\Statsd;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use kuiper\db\Criteria;
use kuiper\helper\Arrays;
use kuiper\helper\Text;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use wenbinye\tars\rpc\exception\ConnectionException;
use wenbinye\tars\rpc\exception\ServerException;
use wenbinye\tars\rpc\TarsClientFactoryInterface;
use winwin\eventBus\client\EventBusSubscriberServant;
use winwin\eventBus\client\Notification;
use winwin\eventBus\constants\EventStatus;
use winwin\eventBus\domain\Event;
use winwin\eventBus\domain\EventRepository;
use winwin\eventBus\domain\Log;
use winwin\eventBus\domain\LogRepository;
use winwin\eventBus\domain\Subscriber;
use winwin\eventBus\domain\SubscriberRepository;
use winwin\eventBus\exceptions\NotifyException;
use winwin\jobQueue\JobFactoryInterface;
use winwin\jobQueue\JobHandlerInterface;

class NotifyJob implements JobHandlerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected const TAG = '['.__CLASS__.'] ';

    private static $RETRY_INTERVALS = [15, 30, 300, 1800];

    /**
     * @var EventRepository
     */
    private $eventRepository;

    /**
     * @var SubscriberRepository
     */
    private $subscriberRepository;

    /**
     * @var LogRepository
     */
    private $logRepository;

    /**
     * @var JobFactoryInterface
     */
    private $jobFactory;

    /**
     * @var ClientInterface
     */
    private $httpClient;

    /**
     * @var TarsClientFactoryInterface
     */
    private $tarsClientFactory;

    /**
     * @var Statsd\Client
     */
    private $statsdClient;

    /**
     * @var EventBusSubscriberServant[]
     */
    private $tarsClients;

    public function __construct(
        EventRepository $eventRepository,
        SubscriberRepository $subscriberRepository,
        LogRepository $logRepository,
        JobFactoryInterface $jobFactory,
        ClientInterface $httpClient,
        TarsClientFactoryInterface $tarsClientFactory,
        Statsd\Client $statsdClient
    ) {
        $this->eventRepository = $eventRepository;
        $this->subscriberRepository = $subscriberRepository;
        $this->logRepository = $logRepository;
        $this->jobFactory = $jobFactory;
        $this->httpClient = $httpClient;
        $this->tarsClientFactory = $tarsClientFactory;
        $this->statsdClient = $statsdClient;
    }

    public function handle(array $arguments): void
    {
        $event = $this->eventRepository->findFirstBy(Criteria::create([
            'eventId' => $arguments['event_id'],
        ]));
        if ($event->getStatus()->isDone()) {
            return;
        }
        $criteria = Criteria::create([
            'topic' => $event->getTopic(),
            'enabled' => true,
        ]);
        if (!empty($arguments['subscribers'])) {
            $criteria->in('notifyUrl', $arguments['subscribers']);
        }
        $subscribers = $this->subscriberRepository->findAllBy($criteria);

        $failed = $this->notifyAll($event, $subscribers);
        if (empty($failed)) {
            $event->setStatus(EventStatus::DONE());
        } elseif ($this->retry($arguments, $failed)) {
            $event->setStatus(EventStatus::RETRY());
        } else {
            $event->setStatus(EventStatus::ERROR());
        }
        $this->eventRepository->update($event);
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
            $log = $this->createLog($event, $subscriber);
            try {
                $this->notify($event, $subscriber);
                $log->setErrorCode(Log::SUCCESS)
                    ->setErrorDesc('');
            } catch (NotifyException $e) {
                $this->logger->error(static::TAG.'notify failed', [
                    'event_id' => $event->getEventId(),
                    'notify_url' => $subscriber->getNotifyUrl(),
                    'error' => $e->getMessage(),
                ]);
                $log->setErrorCode(is_numeric($e->getCode()) && 0 !== $e->getCode()
                    ? $e->getCode()
                    : Log::UNKNOWN_ERROR);
                $log->setErrorDesc(str_replace("\n", ' ', substr((string) $e, 0, Log::MAX_MESSAGE_LEN)));
                $failed[] = $subscriber;
            } finally {
                $log->setResponseTimeByStartTime($startTime);
                $this->logRepository->insert($log);
                if ($log->isSuccess()) {
                    $this->statsdClient->timing(
                        sprintf('eventbus.%s_%s', $event->getTopic(), $event->getEventName()),
                        $log->getResponseTime()
                    );
                }
            }
        }

        return $failed;
    }

    /**
     * @throws NotifyException
     */
    private function notify(Event $event, Subscriber $subscriber)
    {
        $notification = new Notification();
        $notification->createTime = $event->getCreateTime()->format(DATE_ATOM);
        $notification->eventId = $event->getEventId();
        $notification->topic = $event->getTopic();
        $notification->eventName = $event->getEventName();
        $notification->payload = $event->getPayload();

        $this->logger->info(static::TAG.'notify '.$subscriber->getNotifyUrl(), ['eventId' => $notification->eventId]);
        if (Text::startsWith($subscriber->getNotifyUrl(), 'tars://')) {
            $this->notifyByTarRpc($subscriber, $notification);
        } elseif (preg_match('#^https?://#', $subscriber->getNotifyUrl())) {
            $this->notifyByHttp($subscriber, $notification);
        } else {
            throw new \InvalidArgumentException('tars ');
        }
    }

    private function notifyByTarRpc(Subscriber $subscriber, Notification $notification)
    {
        $servantName = substr($subscriber->getNotifyUrl(), strlen('tars://'));
        if (!isset($this->tarsClients[$servantName])) {
            /* @var EventBusSubscriberServant $client */
            $this->tarsClients[$servantName] = $this->tarsClientFactory->create(EventBusSubscriberServant::class, $servantName);
        }
        try {
            $this->tarsClients[$servantName]->handle($notification);
        } catch (ConnectionException | ServerException $e) {
            throw new NotifyException($e->getMessage(), $e->getCode(), $e);
        }
    }

    private function notifyByHttp(Subscriber $subscriber, Notification $notification)
    {
        try {
            $notification->payload = json_decode($notification->payload, true);
            $response = $this->httpClient->request('POST', $subscriber->getNotifyUrl(), [
                'headers' => [
                    'content-type' => 'application/json',
                ],
                'body' => json_encode(Arrays::toArray($notification, false, true)),
            ]);
            $ret = json_decode((string) $response->getBody(), true);
            if (!isset($ret['success']) || true !== $ret['success']) {
                throw new NotifyException('Bad notification response: '.$response->getBody());
            }
        } catch (GuzzleException $e) {
            throw new NotifyException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param Subscriber[] $failed
     */
    private function retry(array $arguments, array $failed): bool
    {
        $retryTimes = ($arguments['retry_times'] ?? 0) + 1;
        $subscribers = Arrays::pull($failed, 'notifyUrl');
        if ($retryTimes >= count(self::$RETRY_INTERVALS)) {
            $this->logger->error(static::TAG.'notify stopped', [
                'event_id' => $arguments['event_id'],
                'subscribers' => $subscribers,
            ]);

            return false;
        }

        $this->jobFactory->create(__CLASS__, array_merge($arguments, [
            'retry_times' => $retryTimes,
            'subscribers' => $subscribers,
        ]))
            ->delay(self::$RETRY_INTERVALS[$retryTimes])
            ->put();

        return true;
    }

    private function createLog(Event $event, Subscriber $subscriber): Log
    {
        $log = new Log();
        $log->setEventId($event->getEventId())
            ->setSubscriberId($subscriber->getId())
            ->setErrorCode(Log::UNKNOWN_ERROR)
            ->setErrorDesc('unknown');

        return $log;
    }
}
