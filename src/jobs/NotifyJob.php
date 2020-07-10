<?php

namespace winwin\eventBus\jobs;

use kuiper\db\Criteria;
use kuiper\helper\Arrays;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use winwin\eventBus\application\EventBusNotifyService;
use winwin\eventBus\constants\EventStatus;
use winwin\eventBus\domain\EventRepository;
use winwin\eventBus\domain\SubscriberRepository;
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
     * @var EventBusNotifyService
     */
    private $eventBusNotifyService;

    /**
     * @var JobFactoryInterface
     */
    private $jobFactory;

    /**
     * NotifyJob constructor.
     */
    public function __construct(
        EventRepository $eventRepository,
        SubscriberRepository $subscriberRepository,
        EventBusNotifyService $eventBusNotifyService,
        JobFactoryInterface $jobFactory)
    {
        $this->eventRepository = $eventRepository;
        $this->subscriberRepository = $subscriberRepository;
        $this->eventBusNotifyService = $eventBusNotifyService;
        $this->jobFactory = $jobFactory;
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

        $failed = $this->eventBusNotifyService->send($event, $subscribers);
        if (empty($failed)) {
            $event->setStatus(EventStatus::DONE());
        } elseif ($this->retry($arguments, $failed)) {
            $event->setStatus(EventStatus::RETRY());
        } else {
            $event->setStatus(EventStatus::ERROR());
        }
        $this->eventRepository->update($event);
    }

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
}
