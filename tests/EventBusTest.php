<?php

namespace winwin\eventBus;

use PHPUnit\DbUnit\DataSet\IDataSet;
use winwin\eventBus\jobs\NotifyJob;
use winwin\eventBus\servant\EventBusServant;
use winwin\jobQueue\Job;
use winwin\jobQueue\JobFactoryInterface;
use winwin\jobQueue\JobQueueInterface;

class EventBusTest extends TestCase
{
    use DatabaseTestCaseTrait;

    /**
     * @var JobFactoryInterface
     */
    private $jobFactory;

    public function testPublish()
    {
        $eventBus = $this->createEventBus();
        $jobQueue = \Mockery::mock(JobQueueInterface::class);
        $jobQueue->shouldReceive('put');
        $this->jobFactory->shouldReceive('create')
            ->once()
            ->with(NotifyJob::class, \Mockery::on(function ($args) {
                // var_export($args);
                $this->assertArrayHasKey('event_id', $args);

                return true;
            }))
            ->andReturn(new Job($jobQueue, '', []));
        $eventBus->publish('merchant', 'open', json_encode(['merchant_id' => 3333]));
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
     * @return EventBusServant
     */
    private function createEventBus()
    {
        return $this->getContainer()->get(EventBusServant::class);
    }

    protected function getDefinitions(): array
    {
        return [
            JobFactoryInterface::class => $this->jobFactory = \Mockery::mock(JobFactoryInterface::class),
        ];
    }
}
