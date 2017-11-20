<?php

namespace winwin\eventBus\facade;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use winwin\eventBus\TestCase;

class CallbackHandlerTest extends TestCase
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function testMiddleware()
    {
        $middleware = $this->createMiddleware();
        $this->eventDispatcher->shouldReceive('dispatch')
            ->once()
            ->with('event_bus.merchant.open', \Mockery::on(function ($event) {
                // var_export($event);
                $this->assertEquals([
                    'merchant_id' => 3333,
                ], $event['payload']);

                return true;
            }));
        $request = new ServerRequest('POST', 'http://localhost/event-bus/notification', [], json_encode([
            'create_time' => '2017-11-20T17:12:59+08:00',
            'event_id' => '0115060e-cdd3-11e7-b85c-02427a6bfbd8',
            'topic' => 'merchant',
            'event_name' => 'open',
            'payload' => [
                'merchant_id' => 3333,
            ],
        ]));
        $response = $middleware($request, new Response(), function () {
            throw new \RuntimeException();
        });
        $this->assertEquals('{"success":true}', (string) $response->getBody());
    }

    /**
     * @return CallbackHandler
     */
    private function createMiddleware()
    {
        $container = $this->getContainer([
            EventDispatcherInterface::class => $this->eventDispatcher = \Mockery::mock(EventDispatcherInterface::class),
        ]);

        return $container->get(CallbackHandler::class);
    }
}
