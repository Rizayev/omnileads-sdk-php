<?php

declare(strict_types=1);

namespace Madtec\OmniLeads\Enums;

use Madtec\OmniLeads\Exceptions\ConfigurationException;

enum SourceType: int
{
    case ALPHA_NAME = 1;
    case PHONE = 2;
    case DOMAIN = 3;

    public function label(): string
    {
        return match ($this) {
            self::ALPHA_NAME => 'Альфа имя',
            self::PHONE => 'Телефон',
            self::DOMAIN => 'Домен',
        };
    }

    public static function fromName(string $name): self
    {
        $normalized = mb_strtolower(trim($name));

        return match ($normalized) {
            'альфа имя', 'альфа-имя', 'alpha_name', 'alpha name', 'alphaname' => self::ALPHA_NAME,
            'телефон', 'phone' => self::PHONE,
            'домен', 'domain' => self::DOMAIN,
            default => throw new ConfigurationException(
                sprintf('Unknown source type: "%s"', $name),
            ),
        };
    }
}
