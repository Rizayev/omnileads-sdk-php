<?php

declare(strict_types=1);

namespace Madtec\OmniLeads\DTO\Responses;

final readonly class WebhookSettings
{
    public function __construct(
        public bool $isEnabled,
        public ?string $webhookUrl,
    ) {}

    /**
     * @param  array<int|string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $url = $data['webhookUrl'] ?? null;

        return new self(
            isEnabled: (bool) ($data['isEnabled'] ?? false),
            webhookUrl: is_string($url) && $url !== '' ? $url : null,
        );
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
