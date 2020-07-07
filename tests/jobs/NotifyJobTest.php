<?php

namespace winwin\eventBus\jobs;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use PHPUnit\DbUnit\DataSet\IDataSet;
use winwin\eventBus\constants\EventStatus;
use winwin\eventBus\DatabaseTestCaseTrait;
use winwin\eventBus\TestCase;
use winwin\jobQueue\Job;
use winwin\jobQueue\JobFactoryInterface;
use winwin\jobQueue\JobQueueInterface;

class NotifyJobTest extends TestCase
{
    use DatabaseTestCaseTrait;

    /**
     * @var JobQueueInterface
     */
    private $jobFactory;

    /**
     * @var ClientInterface
     */
    private $httpClient;

    public function testProcess()
    {
        $handler = $this->createJobHandler();
        $this->httpClient->shouldReceive('request')
            ->once()
            ->with('POST', 'http://localhost/event-bus/notification', \Mockery::on(function ($options) {
                // var_export($options);
                $body = json_decode($options['body'], true);
                $this->assertEquals([
                    'create_time' => '2017-11-20T17:12:59+08:00',
                    'event_id' => '0115060e-cdd3-11e7-b85c-02427a6bfbd8',
                    'topic' => 'merchant',
                    'event_name' => 'open',
                    'payload' => [
                        'merchant_id' => 3333,
                    ],
                ], $body);

                return true;
            }))
            ->andReturn(new Response(200, [], '{"success":true}'));
        $this->jobFactory->shouldNotReceive('create');
        $handler->handle(['event_id' => '0115060e-cdd3-11e7-b85c-02427a6bfbd8']);
        $row = $this->getConnection()->createQueryTable('event', 'select * from eventbus_event')->getRow(0);
        $this->assertEquals(EventStatus::DONE, $row['status']);
        $row = $this->getConnection()->createQueryTable('log', 'select * from eventbus_log')->getRow(0);
        $this->assertEquals(1, $row['subscriber_id']);
        // print_r($row);
    }

    public function testRetry()
    {
        $handler = $this->createJobHandler();
        $this->httpClient->shouldReceive('request')
            ->once()
            ->andReturn(new Response(200, [], '{}'));
        $jobQueue = \Mockery::mock(JobQueueInterface::class);
        $jobQueue->shouldReceive('put');
        $job = new Job($jobQueue, '', []);
        $this->jobFactory->shouldReceive('create')
            ->once()
            ->with(NotifyJob::class, \Mockery::on(function ($args) {
                // var_export($args);
                $this->assertArrayHasKey('subscribers', $args);

                return true;
            }))
        ->andReturn($job);
        $handler->handle(['event_id' => '0115060e-cdd3-11e7-b85c-02427a6bfbd8']);
        $row = $this->getConnection()->createQueryTable('event', 'select * from eventbus_event')->getRow(0);
        $this->assertEquals(EventStatus::RETRY, $row['status']);
        $row = $this->getConnection()->createQueryTable('log', 'select * from eventbus_log')->getRow(0);
        // print_r($row);
        $this->assertEquals(-1, $row['error_code']);
    }

    protected function createJobHandler()
    {
        return $this->getContainer()->get(NotifyJob::class);
    }

    protected function getDefinitions(): array
    {
        return [
            JobFactoryInterface::class => $this->jobFactory = \Mockery::mock(JobFactoryInterface::class),
            ClientInterface::class => $this->httpClient = \Mockery::mock(ClientInterface::class),
        ];
    }

    public function testRetryOk()
    {
        $handler = $this->createJobHandler();
        $this->httpClient->shouldReceive('request')
            ->once()
            ->andReturn(new Response(200, [], '{"success":true}'));
        $this->jobFactory->shouldNotReceive('put');
        $handler->handle([
            'event_id' => '0115060e-cdd3-11e7-b85c-02427a6bfbd8',
            'retry_times' => 1,
            'subscribers' => ['http://localhost/event-bus/notification'],
        ]);
        $row = $this->getConnection()->createQueryTable('event', 'select * from eventbus_event')->getRow(0);
        $this->assertEquals(EventStatus::DONE, $row['status']);
    }

    /**
     * Returns the test dataset.
     *
     * @return IDataSet
     */
    protected function getDataSet()
    {
        return $this->createArrayDataSet([
            'eventbus_event' => [
                [
                    'id' => '1',
                    'update_time' => '2017-11-20 17:12:59',
                    'create_time' => '2017-11-20 17:12:59',
                    'event_id' => '0115060e-cdd3-11e7-b85c-02427a6bfbd8',
                    'event_name' => 'open',
                    'topic' => 'merchant',
                    'payload' => '{"merchant_id":3333}',
                ],
            ],
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
            'eventbus_log' => [],
        ]);
    }
}
