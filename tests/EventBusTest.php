<?php

namespace winwin\eventBus;

use PHPUnit\DbUnit\DataSet\IDataSet;
use winwin\eventBus\facade\EventBusInterface;
use winwin\eventBus\jobs\NotifyJob;
use winwin\jobQueue\JobQueueInterface;

class EventBusTest extends TestCase
{
    use DatabaseTestCaseTrait;

    /**
     * @var JobQueueInterface
     */
    private $jobQueue;

    public function testPublish()
    {
        $eventBus = $this->createEventBus();
        $this->jobQueue->shouldReceive('put')
            ->once()
            ->with(NotifyJob::class, \Mockery::on(function ($args) {
                // var_export($args);
                $this->assertArrayHasKey('event_id', $args);

                return true;
            }));
        $eventBus->publish('merchant', 'open', ['merchant_id' => 3333]);
        $this->assertTableRowCount('eventbus_event', 1);
    }

    /**
     * Returns the test dataset.
     *
     * @return IDataSet
     */
    protected function getDataSet()
    {
        // TODO: Implement getDataSet() method.

        return $this->createArrayDataSet([
            'eventbus_event' => [],
        ]);
    }

    /**
     * @return EventBusInterface
     */
    private function createEventBus()
    {
        $container = $this->getContainer([
            'eventBus.JobQueue' => $this->jobQueue = \Mockery::mock(JobQueueInterface::class),
        ]);

        return $container->get(EventBusInterface::class);
    }
}
