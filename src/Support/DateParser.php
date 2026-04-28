<?php

declare(strict_types=1);

namespace Madtec\OmniLeads\Support;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Madtec\OmniLeads\Exceptions\ConfigurationException;

final class DateParser
{
    public static function parse(mixed $value): ?DateTimeImmutable
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof DateTimeImmutable) {
            return $value->setTimezone(new DateTimeZone('UTC'));
        }

        if ($value instanceof DateTimeInterface) {
            return DateTimeImmutable::createFromInterface($value)
                ->setTimezone(new DateTimeZone('UTC'));
        }

        if (! is_string($value)) {
            return null;
        }

        try {
            $date = new DateTimeImmutable($value);
        } catch (\Exception) {
            return null;
        }

        return $date->setTimezone(new DateTimeZone('UTC'));
    }

    public static function format(DateTimeImmutable $date): string
    {
        return $date->setTimezone(new DateTimeZone('UTC'))->format(DATE_ATOM);
    }

    public static function assertIsoDate(string $date, string $field = 'date'): void
    {
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            throw new ConfigurationException(
                sprintf('%s must be in YYYY-MM-DD format, got "%s"', $field, $date),
            );
        }

        $parts = explode('-', $date);
        $year = (int) $parts[0];
        $month = (int) $parts[1];
        $day = (int) $parts[2];

        if (! checkdate($month, $day, $year)) {
            throw new ConfigurationException(
                sprintf('%s contains an invalid calendar date: "%s"', $field, $date),
            );
        }
    }
}
