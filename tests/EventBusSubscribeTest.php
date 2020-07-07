<?php

namespace winwin\eventBus;

use InvalidArgumentException;
use PHPUnit\DbUnit\DataSet\IDataSet;
use winwin\eventBus\servant\EventBusServant;

class EventBusSubscribeTest extends TestCase
{
    use DatabaseTestCaseTrait;

    public function testSubscribe()
    {
        $eventBus = $this->createEventBus();
        $eventBus->subscribe('merchant', 'http://localhost:8000/event-bus/notification');
        $this->assertTableRowCount('eventbus_subscriber', 2);
    }

    public function testAlreadySubscribe()
    {
        $this->expectException(InvalidArgumentException::class);
        $eventBus = $this->createEventBus();
        $eventBus->subscribe('merchant', 'http://localhost/event-bus/notification');
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
            'eventbus_subscriber' => [
                [
                    'id' => '1',
                    'update_time' => '2017-11-20 17:14:34',
                    'create_time' => '2017-11-20 17:14:34',
                    'topic' => 'merchant',
                    'notify_url' => 'http://localhost/event-bus/notification',
                    'enabled' => 1,
                ],
            ],
        ]);
    }

    /**
     * @return EventBusServant
     */
    private function createEventBus()
    {
        return $this->getContainer()->get(EventBusServant::class);
    }
}
