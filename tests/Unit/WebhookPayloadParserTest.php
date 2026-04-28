<?php

declare(strict_types=1);

use Madtec\OmniLeads\Exceptions\ValidationException;
use Madtec\OmniLeads\Webhook\WebhookPayloadParser;

test('parser accepts valid payload as string', function (): void {
    $payload = json_encode([
        'eventType' => 'import.completed',
        'occurredAt' => '2026-03-23T10:12:45Z',
        'userId' => 'user-123',
        'userEmail' => 'partner@example.com',
        'importType' => 'phones',
        'totalProjects' => 1,
        'totalSegments' => 1,
        'totalPhones' => 2,
        'projects' => [
            [
                'projectId' => 'guid',
                'projectName' => 'CRM import',
                'segments' => [
                    [
                        'projectSourceId' => 145,
                        'sourceTypeId' => 1,
                        'sourceTypeName' => 'Домен',
                        'sourceDataId' => 8,
                        'sourceDataName' => 'Partner A',
                        'sourceValues' => ['example.com'],
                        'phones' => [
                            ['phone' => 79001234567, 'createdAt' => '2026-03-23T10:12:40Z'],
                        ],
                    ],
                ],
            ],
        ],
    ], JSON_THROW_ON_ERROR);

    $parser = new WebhookPayloadParser;
    $event = $parser->parse($payload);

    expect($event->eventType)->toBe('import.completed')
        ->and($event->totalPhones)->toBe(2)
        ->and($event->projects)->toHaveCount(1)
        ->and($event->projects[0]->segments[0]->phones[0]->phone)->toBe('79001234567');
});

test('parser accepts array payload', function (): void {
    $parser = new WebhookPayloadParser;

    $event = $parser->parse([
        'eventType' => 'import.completed',
        'occurredAt' => '2026-03-23T10:12:45Z',
        'userId' => 'u',
        'importType' => 'phones',
        'projects' => [],
    ]);

    expect($event->userId)->toBe('u')
        ->and($event->projects)->toBe([]);
});

test('parser rejects invalid JSON', function (): void {
    (new WebhookPayloadParser)->parse('{invalid json');
})->throws(ValidationException::class);

test('parser rejects missing fields', function (): void {
    (new WebhookPayloadParser)->parse([
        'eventType' => 'import.completed',
    ]);
})->throws(ValidationException::class);

test('parser rejects unsupported event type', function (): void {
    (new WebhookPayloadParser)->parse([
        'eventType' => 'something.else',
        'occurredAt' => '2026-01-01T00:00:00Z',
        'userId' => 'u',
        'importType' => 'phones',
    ]);
})->throws(ValidationException::class);

test('parser rejects empty body', function (): void {
    (new WebhookPayloadParser)->parse('');
})->throws(ValidationException::class);
