<?php

namespace winwin\eventBus;

use PHPUnit\DbUnit\DataSet\IDataSet;
use winwin\eventBus\facade\EventBusInterface;

class EventBusSubscribeTest extends TestCase
{
    use DatabaseTestCaseTrait;

    public function testSubscribe()
    {
        $eventBus = $this->createEventBus();
        $eventBus->subscribe('merchant', 'http://localhost:8000/event-bus/notification');
        $this->assertTableRowCount('eventbus_subscriber', 2);
    }

    /**
     * @expectedException \winwin\eventBus\facade\exception\AlreadySubscribedException
     */
    public function testAlreadySubscribe()
    {
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
                ],
            ],
        ]);
    }

    /**
     * @return EventBusInterface
     */
    private function createEventBus()
    {
        $container = $this->getContainer([
        ]);

        return $container->get(EventBusInterface::class);
    }
}
