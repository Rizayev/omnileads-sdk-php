<?php

declare(strict_types=1);

namespace Madtec\OmniLeads\Webhook;

use Madtec\OmniLeads\Exceptions\ValidationException;

final class WebhookPayloadParser
{
    private const REQUIRED_FIELDS = [
        'eventType',
        'occurredAt',
        'userId',
        'importType',
    ];

    private const SUPPORTED_EVENT_TYPES = [
        'import.completed',
    ];

    /**
     * @param  string|array<string, mixed>  $payload
     */
    public function parse(string|array $payload): ImportCompletedEvent
    {
        $data = is_array($payload) ? $payload : $this->decodeJson($payload);

        $this->assertStructure($data);

        return ImportCompletedEvent::fromArray($data);
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeJson(string $body): array
    {
        if ($body === '') {
            throw new ValidationException(
                statusCode: 0,
                responseBody: $body,
                requestMethod: 'WEBHOOK',
                requestUri: 'webhook',
                errorMessage: 'Webhook payload is empty',
            );
        }

        $decoded = json_decode($body, true);

        if (! is_array($decoded)) {
            throw new ValidationException(
                statusCode: 0,
                responseBody: $body,
                requestMethod: 'WEBHOOK',
                requestUri: 'webhook',
                errorMessage: 'Webhook payload is not valid JSON object: '.json_last_error_msg(),
            );
        }

        return $decoded;
    }

    /**
     * @param  array<int|string, mixed>  $data
     */
    private function assertStructure(array $data): void
    {
        foreach (self::REQUIRED_FIELDS as $field) {
            if (! array_key_exists($field, $data)) {
                throw new ValidationException(
                    statusCode: 0,
                    responseBody: '',
                    requestMethod: 'WEBHOOK',
                    requestUri: 'webhook',
                    errorMessage: sprintf('Webhook payload missing required field: "%s"', $field),
                );
            }
        }

        $eventType = $data['eventType'];
        if (! is_string($eventType) || ! in_array($eventType, self::SUPPORTED_EVENT_TYPES, true)) {
            throw new ValidationException(
                statusCode: 0,
                responseBody: '',
                requestMethod: 'WEBHOOK',
                requestUri: 'webhook',
                errorMessage: sprintf(
                    'Unsupported webhook event type: "%s"',
                    is_scalar($eventType) ? (string) $eventType : 'invalid',
                ),
            );
        }

        if (isset($data['projects']) && ! is_array($data['projects'])) {
            throw new ValidationException(
                statusCode: 0,
                responseBody: '',
                requestMethod: 'WEBHOOK',
                requestUri: 'webhook',
                errorMessage: 'Webhook payload "projects" must be an array',
            );
        }
    }
}
