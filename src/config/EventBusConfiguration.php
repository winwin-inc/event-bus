<?php

namespace winwin\eventBus\config;

use DI\Annotation\Inject;
use Domnikl\Statsd\Client;
use Domnikl\Statsd\Connection\UdpSocket;
use kuiper\di\annotation\Bean;
use kuiper\di\annotation\Configuration;

/**
 * Class EventBusConfiguration.
 *
 * @Configuration()
 */
class EventBusConfiguration
{
    /**
     * @Bean()
     * @Inject({"config": "application.statsd"})
     */
    public function statsdClient(array $config): Client
    {
        $connection = new UdpSocket($config['host'], $config['port'] ?? 8125);

        return new Client($connection);
    }
}
