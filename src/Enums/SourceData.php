<?php

declare(strict_types=1);

namespace Madtec\OmniLeads\Enums;

use Madtec\OmniLeads\Exceptions\ConfigurationException;

enum SourceData: int
{
    case TELE2 = 4;
    case MTS = 5;
    case BEELINE = 6;
    case ROSTELECOM = 7;
    case MEGAFON = 8;

    public function label(): string
    {
        return match ($this) {
            self::TELE2 => 'Теле2',
            self::MTS => 'МТС',
            self::BEELINE => 'Билайн',
            self::ROSTELECOM => 'Ростелеком',
            self::MEGAFON => 'Мегафон',
        };
    }

    public static function fromName(string $name): self
    {
        $normalized = mb_strtolower(trim($name));

        return match ($normalized) {
            'теле2', 'tele2' => self::TELE2,
            'мтс', 'mts' => self::MTS,
            'билайн', 'beeline' => self::BEELINE,
            'ростелеком', 'rostelecom' => self::ROSTELECOM,
            'мегафон', 'megafon' => self::MEGAFON,
            default => throw new ConfigurationException(
                sprintf('Unknown source data: "%s"', $name),
            ),
        };
    }
}
