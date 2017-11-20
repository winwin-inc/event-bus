<?php

namespace winwin\eventBus;

use kuiper\boot\Provider;
use kuiper\di;
use winwin\db\orm\RepositoryFactory;
use winwin\eventBus\facade\EventBusInterface;
use winwin\eventBus\models\Event;
use winwin\eventBus\models\Subscriber;
use winwin\jobQueue\JobQueue;

class EventBusServiceProvider extends Provider
{
    /**
     * Registers services.
     */
    public function register()
    {
        $this->services->addDefinitions([
            'eventBus.EventRepository' => di\factory([di\get(RepositoryFactory::class), 'create'], Event::class),
            'eventBus.SubscriberRepository' => di\factory([di\get(RepositoryFactory::class), 'create'], Subscriber::class),
            'eventBus.JobQueue' => di\object(JobQueue::class)
            ->constructor(di\get('app.beanstalk.host'), di\get('app.beanstalk.port'), di\get('event_bus.beanstalk.tube')),

            EventBusInterface::class => di\object(EventBus::class),
        ]);
    }
}
