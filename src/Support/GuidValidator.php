<?php

declare(strict_types=1);

namespace Madtec\OmniLeads\Support;

use Madtec\OmniLeads\Exceptions\ConfigurationException;

final class GuidValidator
{
    private const PATTERN = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';

    public static function isValid(string $guid): bool
    {
        return preg_match(self::PATTERN, $guid) === 1;
    }

    public static function assert(string $guid, string $field = 'id'): void
    {
        if (! self::isValid($guid)) {
            throw new ConfigurationException(
                sprintf('%s must be a valid GUID, got "%s"', $field, $guid),
            );
        }
    }
}
