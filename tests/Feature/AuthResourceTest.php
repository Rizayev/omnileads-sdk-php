<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\Response;
use Madtec\OmniLeads\Config\OmniLeadsConfig;
use Madtec\OmniLeads\Config\RetryConfig;
use Madtec\OmniLeads\DTO\Requests\WebhookSettingsRequest;
use Madtec\OmniLeads\Exceptions\ConfigurationException;
use Madtec\OmniLeads\OmniLeadsClient;

test('getWebhookSettings sends Bearer auth', function (): void {
    $client = $this->buildClient([
        new Response(200, [], json_encode([
            'isEnabled' => true,
            'webhookUrl' => 'https://example.com/webhook',
        ], JSON_THROW_ON_ERROR)),
    ]);

    $settings = $client->auth()->getWebhookSettings();

    expect($settings->isEnabled)->toBeTrue()
        ->and($settings->webhookUrl)->toBe('https://example.com/webhook');

    expect($this->history[0]['request']->getHeaderLine('Authorization'))
        ->toBe('Bearer test-jwt');
});

test('updateWebhookSettings PUTs body', function (): void {
    $client = $this->buildClient([
        new Response(200, [], json_encode([
            'isEnabled' => false,
            'webhookUrl' => null,
        ], JSON_THROW_ON_ERROR)),
    ]);

    $settings = $client->auth()->updateWebhookSettings(
        new WebhookSettingsRequest(isEnabled: false, webhookUrl: null),
    );

    expect($settings->isEnabled)->toBeFalse()
        ->and($settings->webhookUrl)->toBeNull()
        ->and($this->history[0]['request']->getMethod())->toBe('PUT');
});

test('webhook call without JWT throws ConfigurationException before HTTP', function (): void {
    $config = new OmniLeadsConfig(
        baseUrl: 'https://api.test.local/api',
        apiKey: 'test-key',
        jwt: null,
        timeout: 30,
        retry: new RetryConfig(false, 1, 0),
    );

    $client = new OmniLeadsClient(config: $config);

    $client->auth()->getWebhookSettings();
})->throws(ConfigurationException::class);
