<?php

declare(strict_types=1);

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;
use Madtec\OmniLeads\Config\RetryConfig;
use Madtec\OmniLeads\Exceptions\ApiException;

test('network error wraps into ApiException with statusCode 0', function (): void {
    $client = $this->buildClient([
        new ConnectException('Connection refused', new Request('GET', '/Project')),
    ]);

    try {
        $client->projects()->list();
        expect(false)->toBeTrue('Expected ApiException');
    } catch (ApiException $e) {
        expect($e->statusCode)->toBe(0)
            ->and($e->errorMessage)->toContain('Connection refused');
    }
});

test('network errors are retried when retry enabled', function (): void {
    $client = $this->buildClient([
        new ConnectException('Connection refused', new Request('GET', '/Project')),
        new ConnectException('Connection refused', new Request('GET', '/Project')),
        new ConnectException('Connection refused', new Request('GET', '/Project')),
    ], [
        'retry' => new RetryConfig(true, 3, 0),
    ]);

    try {
        $client->projects()->list();
    } catch (ApiException $e) {
        expect($this->history)->toHaveCount(3);
    }
});
