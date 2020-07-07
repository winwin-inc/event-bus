<?php

namespace winwin\eventBus\application;

use Carbon\Carbon;
use kuiper\db\Criteria;
use kuiper\di\annotation\Service;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Webmozart\Assert\Assert;
use wenbinye\tars\exception\ValidationException;
use winwin\eventBus\constants\EventStatus;
use winwin\eventBus\domain\Event;
use winwin\eventBus\domain\EventRepository;
use winwin\eventBus\domain\LogRepository;
use winwin\eventBus\domain\Subscriber;
use winwin\eventBus\domain\SubscriberRepository;
use winwin\eventBus\jobs\NotifyJob;
use winwin\eventBus\servant\EventBusServant;
use winwin\jobQueue\JobFactoryInterface;

/**
 * @Service()
 */
class EventBusServantImpl implements EventBusServant, LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected const TAG = '['.__CLASS__.'] ';
    private const BATCH_SIZE = 2000;

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
     * @var ValidatorInterface
     */
    private $validator;

    public function __construct(
        EventRepository $eventRepository,
        SubscriberRepository $subscriberRepository,
        LogRepository $logRepository,
        JobFactoryInterface $jobFactory,
        ValidatorInterface $validator)
    {
        $this->eventRepository = $eventRepository;
        $this->subscriberRepository = $subscriberRepository;
        $this->logRepository = $logRepository;
        $this->jobFactory = $jobFactory;
        $this->validator = $validator;
    }

    /**
     * {@inheritdoc}
     */
    public function publish($topic, $eventName, $payload)
    {
        $event = new Event();
        $event->setEventId(Uuid::uuid1())
            ->setTopic($topic)
            ->setEventName($eventName)
            ->setPayload($payload)
            ->setStatus(EventStatus::CREATE());
        $violations = $this->validator->validate($event);
        if ($violations->count() > 0) {
            throw new ValidationException($violations);
        }
        $this->eventRepository->insert($event);
        $this->jobFactory->create(NotifyJob::class, [
            'event_id' => $event->getEventId(),
        ])->put();

        return $event->getEventId();
    }

    /**
     * {@inheritdoc}
     */
    public function subscribe($topic, $handler)
    {
        $subscriber = new Subscriber();
        $subscriber->setTopic($topic)
            ->setNotifyUrl($handler)
            ->setEnabled(true);
        $violations = $this->validator->validate($subscriber);
        if ($violations->count() > 0) {
            throw new ValidationException($violations);
        }
        if (!preg_match('#^(https?|tars)://#', $handler)) {
            throw new \InvalidArgumentException("handler url '$handler' should begin with http:// or tars://");
        }

        $exist = $this->subscriberRepository->findFirstBy(Criteria::create([
            'topic' => $topic,
            'notifyUrl' => $handler,
        ]));
        if ($exist) {
            if ($exist->isEnabled()) {
                throw new \InvalidArgumentException("Topic '$topic' was already subscribed by '$handler'");
            }
            $exist->setEnabled(true);
            $this->subscriberRepository->update($exist);
        } else {
            $this->subscriberRepository->insert($subscriber);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function unsubscribe($topic, $handler)
    {
        $subscriber = $this->subscriberRepository->findFirstBy(Criteria::create([
            'topic' => $topic,
            'notifyUrl' => $handler,
        ]));
        if ($subscriber && $subscriber->isEnabled()) {
            $subscriber->setEnabled(false);
            $this->subscriberRepository->update($subscriber);

            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function purge($keepDays)
    {
        Assert::greaterThanEq($keepDays, 2, 'keep days should less than %2$s, got %s');
        $keepTime = Carbon::now()->subDays($keepDays);
        $events = 0;
        do {
            $criteria = Criteria::create(['status' => EventStatus::DONE()])
                ->where('createTime', $keepTime, '<')
                ->limit(self::BATCH_SIZE);

            $this->eventRepository->deleteAllBy($criteria);
            $rows = $this->eventRepository->getLastStatement()->rowCount();
            $events += $rows;
        } while ($rows > 0);
        $this->logger->info(static::TAG."清除过期的事件成功，共删除 $events 条记录");

        $logs = 0;
        do {
            $criteria = Criteria::create()
                ->where('createTime', $keepTime, '<')
                ->limit(self::BATCH_SIZE);

            $this->logRepository->deleteAllBy($criteria);
            $rows = $this->logRepository->getLastStatement()->rowCount();
            $logs += $rows;
        } while ($rows > 0);
        $this->logger->info(static::TAG."清除 $logs 条日志记录");

        return $events;
    }
}
