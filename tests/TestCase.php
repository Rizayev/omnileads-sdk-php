<?php

declare(strict_types=1);

namespace Madtec\OmniLeads\Tests;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Madtec\OmniLeads\Config\OmniLeadsConfig;
use Madtec\OmniLeads\Config\RetryConfig;
use Madtec\OmniLeads\OmniLeadsClient;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use Throwable;

abstract class TestCase extends PHPUnitTestCase
{
    /**
     * @var list<array<string, mixed>>
     */
    protected array $history = [];

    /**
     * @param  list<Response|Throwable>  $responses
     * @param  array<string, mixed>  $configOverrides
     */
    protected function buildClient(array $responses, array $configOverrides = []): OmniLeadsClient
    {
        $mock = new MockHandler($responses);
        $stack = HandlerStack::create($mock);

        $this->history = [];
        $stack->push(Middleware::history($this->history));

        $baseUrl = isset($configOverrides['base_url']) && is_string($configOverrides['base_url'])
            ? $configOverrides['base_url']
            : 'https://api.test.local/api';

        $guzzle = new GuzzleClient([
            'base_uri' => $baseUrl.'/',
            'handler' => $stack,
            'http_errors' => true,
        ]);

        $retry = $configOverrides['retry'] ?? new RetryConfig(false, 1, 0);
        if (! $retry instanceof RetryConfig) {
            $retry = new RetryConfig(false, 1, 0);
        }

        $config = new OmniLeadsConfig(
            baseUrl: $baseUrl,
            apiKey: isset($configOverrides['api_key']) && is_string($configOverrides['api_key'])
                ? $configOverrides['api_key']
                : 'test-api-key',
            jwt: array_key_exists('jwt', $configOverrides)
                ? (is_string($configOverrides['jwt']) ? $configOverrides['jwt'] : null)
                : 'test-jwt',
            timeout: isset($configOverrides['timeout']) && is_int($configOverrides['timeout'])
                ? $configOverrides['timeout']
                : 30,
            retry: $retry,
        );

        return new OmniLeadsClient(
            config: $config,
            guzzle: $guzzle,
        );
    }
}
