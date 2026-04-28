<?php

declare(strict_types=1);

namespace Madtec\OmniLeads\Enums;

use Madtec\OmniLeads\Exceptions\ConfigurationException;

enum ProjectState: string
{
    case ACTIVE = 'active';
    case PAUSED = 'paused';

    public static function fromInput(?string $value): self
    {
        if ($value === null) {
            return self::ACTIVE;
        }

        $normalized = mb_strtolower(trim($value));
        if ($normalized === '') {
            return self::ACTIVE;
        }

        return match ($normalized) {
            'active' => self::ACTIVE,
            'paused' => self::PAUSED,
            default => throw new ConfigurationException(
                sprintf('Invalid project state: "%s". Allowed: active, paused.', $value),
            ),
        };
    }

    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }
}
