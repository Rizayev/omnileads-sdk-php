<?php

declare(strict_types=1);

namespace Madtec\OmniLeads\DTO\Requests;

use Madtec\OmniLeads\Exceptions\ConfigurationException;

final readonly class WebhookSettingsRequest
{
    public function __construct(
        public bool $isEnabled,
        public ?string $webhookUrl,
    ) {
        if ($isEnabled && ($webhookUrl === null || trim($webhookUrl) === '')) {
            throw new ConfigurationException(
                'WebhookSettingsRequest: webhookUrl must be provided when isEnabled = true',
            );
        }

        if ($webhookUrl !== null && $webhookUrl !== '' && filter_var($webhookUrl, FILTER_VALIDATE_URL) === false) {
            throw new ConfigurationException(
                sprintf('WebhookSettingsRequest: invalid webhookUrl "%s"', $webhookUrl),
            );
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'isEnabled' => $this->isEnabled,
            'webhookUrl' => $this->webhookUrl,
        ];
    }
}
