<?php

declare(strict_types=1);

namespace Madtec\OmniLeads\Config;

use Madtec\OmniLeads\Exceptions\ConfigurationException;

final readonly class RetryConfig
{
    public function __construct(
        public bool $enabled,
        public int $times,
        public int $sleepMs,
    ) {
        if ($times < 1) {
            throw new ConfigurationException('retry.times must be >= 1');
        }

        if ($sleepMs < 0) {
            throw new ConfigurationException('retry.sleep_ms must be >= 0');
        }
    }

    /**
     * @param  array<string, mixed>  $config
     */
    public static function fromArray(array $config): self
    {
        $enabled = $config['enabled'] ?? true;
        $times = $config['times'] ?? 3;
        $sleepMs = $config['sleep_ms'] ?? 200;

        return new self(
            enabled: (bool) $enabled,
            times: (int) $times,
            sleepMs: (int) $sleepMs,
        );
    }

    public static function disabled(): self
    {
        return new self(enabled: false, times: 1, sleepMs: 0);
    }
}
