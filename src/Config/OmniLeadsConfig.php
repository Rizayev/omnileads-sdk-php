<?php

declare(strict_types=1);

namespace Madtec\OmniLeads\Config;

use Madtec\OmniLeads\Exceptions\ConfigurationException;

final readonly class OmniLeadsConfig
{
    public function __construct(
        public string $baseUrl,
        public ?string $apiKey,
        public ?string $jwt,
        public int $timeout,
        public RetryConfig $retry,
    ) {
        if (trim($baseUrl) === '') {
            throw new ConfigurationException('OmniLeads base_url must not be empty');
        }

        if (! preg_match('~^https?://~i', $baseUrl)) {
            throw new ConfigurationException('OmniLeads base_url must start with http:// or https://');
        }

        if ($timeout < 1) {
            throw new ConfigurationException('OmniLeads timeout must be >= 1');
        }
    }

    /**
     * @param  array<string, mixed>  $config
     */
    public static function fromArray(array $config): self
    {
        $baseUrl = $config['base_url'] ?? null;
        if (! is_string($baseUrl) || $baseUrl === '') {
            throw new ConfigurationException('Missing or invalid base_url in OmniLeads config');
        }

        $retryConfig = $config['retry'] ?? [];
        if (! is_array($retryConfig)) {
            $retryConfig = [];
        }

        return new self(
            baseUrl: rtrim($baseUrl, '/'),
            apiKey: self::nullableString($config['api_key'] ?? null),
            jwt: self::nullableString($config['jwt'] ?? null),
            timeout: (int) ($config['timeout'] ?? 30),
            retry: RetryConfig::fromArray($retryConfig),
        );
    }

    public function withJwt(?string $jwt): self
    {
        return new self(
            baseUrl: $this->baseUrl,
            apiKey: $this->apiKey,
            jwt: $jwt,
            timeout: $this->timeout,
            retry: $this->retry,
        );
    }

    public function withApiKey(?string $apiKey): self
    {
        return new self(
            baseUrl: $this->baseUrl,
            apiKey: $apiKey,
            jwt: $this->jwt,
            timeout: $this->timeout,
            retry: $this->retry,
        );
    }

    private static function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (! is_string($value)) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }
}
