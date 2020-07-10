<?php

namespace winwin\eventBus\application;

use Domnikl\Statsd\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use kuiper\di\annotation\Service;
use kuiper\helper\Arrays;
use kuiper\helper\Text;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use wenbinye\tars\rpc\exception\ConnectionException;
use wenbinye\tars\rpc\exception\ServerException;
use wenbinye\tars\rpc\TarsClientFactoryInterface;
use winwin\eventBus\client\EventBusSubscriberServant;
use winwin\eventBus\client\Notification;
use winwin\eventBus\domain\Event;
use winwin\eventBus\domain\Log;
use winwin\eventBus\domain\LogRepository;
use winwin\eventBus\domain\Subscriber;
use winwin\eventBus\exceptions\NotifyException;

/**
 * @Service()
 */
class EventBusNotifyServiceImpl implements EventBusNotifyService, LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected const TAG = '['.__CLASS__.'] ';

    /**
     * @var LogRepository
     */
    private $logRepository;

    /**
     * @var ClientInterface
     */
    private $httpClient;

    /**
     * @var TarsClientFactoryInterface
     */
    private $tarsClientFactory;

    /**
     * @var Client
     */
    private $statsdClient;

    /**
     * @var EventBusSubscriberServant[]
     */
    private $tarsClients;

    /**
     * EventBusNotifyServiceImpl constructor.
     */
    public function __construct(
        LogRepository $logRepository,
        ClientInterface $httpClient,
        TarsClientFactoryInterface $tarsClientFactory,
        Client $statsdClient)
    {
        $this->logRepository = $logRepository;
        $this->httpClient = $httpClient;
        $this->tarsClientFactory = $tarsClientFactory;
        $this->statsdClient = $statsdClient;
    }

    /**
     * {@inheritdoc}
     */
    public function send(Event $event, array $subscribers): array
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
