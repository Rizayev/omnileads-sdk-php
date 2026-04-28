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
use Madtec\OmniLeads\OmniLeadsServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    /**
     * @var list<array<string, mixed>>
     */
    protected array $history = [];

    /**
     * @param  list<Response|\Throwable>  $responses
     */
    protected function buildClient(array $responses, array $configOverrides = []): OmniLeadsClient
    {
        $mock = new MockHandler($responses);
        $stack = HandlerStack::create($mock);

        $this->history = [];
        $stack->push(Middleware::history($this->history));

        $guzzle = new GuzzleClient([
            'base_uri' => ($configOverrides['base_url'] ?? 'https://api.test.local/api').'/',
            'handler' => $stack,
            'http_errors' => true,
        ]);

        $config = new OmniLeadsConfig(
            baseUrl: $configOverrides['base_url'] ?? 'https://api.test.local/api',
            apiKey: $configOverrides['api_key'] ?? 'test-api-key',
            jwt: $configOverrides['jwt'] ?? 'test-jwt',
            timeout: (int) ($configOverrides['timeout'] ?? 30),
            retry: $configOverrides['retry'] ?? new RetryConfig(false, 1, 0),
        );

        return new OmniLeadsClient(
            config: $config,
            guzzle: $guzzle,
        );
    }

    /**
     * @return array<int, string>
     */
    protected function loadProviders($app): array
    {
        return [OmniLeadsServiceProvider::class];
    }

    protected function getPackageProviders($app): array
    {
        return [OmniLeadsServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('omnileads.base_url', 'https://api.test.local/api');
        $app['config']->set('omnileads.api_key', 'test-api-key');
        $app['config']->set('omnileads.jwt', 'test-jwt');
        $app['config']->set('omnileads.retry.enabled', false);
    }
}
