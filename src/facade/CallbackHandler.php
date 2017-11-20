<?php

namespace winwin\eventBus\facade;

use kuiper\web\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class CallbackHandler implements MiddlewareInterface
{
    /**
     * @var string
     */
    private $handlerUri;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * CallbackHandler constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param string                   $handlerUri
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, $handlerUri = '/event-bus/notification')
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->handlerUri = $handlerUri;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     * @param callable               $next
     *
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        if ($request->getMethod() == 'POST' && $request->getUri()->getPath() == $this->handlerUri) {
            $message = json_decode((string) $request->getBody(), true);
            $event = new GenericEvent($request, $message);
            $this->eventDispatcher->dispatch(
                sprintf('event_bus.%s.%s', $message['topic'], $message['event_name']), $event);
            $response->getBody()->write(json_encode(['success' => true]));

            return $response;
        } else {
            return $next($request, $response);
        }
    }
}
