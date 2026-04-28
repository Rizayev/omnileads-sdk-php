<?php

declare(strict_types=1);

use Madtec\OmniLeads\Enums\DayOfWeek;
use Madtec\OmniLeads\Enums\GeoRegions;
use Madtec\OmniLeads\Enums\ProjectState;
use Madtec\OmniLeads\Enums\SourceData;
use Madtec\OmniLeads\Enums\SourceType;
use Madtec\OmniLeads\Exceptions\ConfigurationException;

test('SourceType ids are stable', function (): void {
    expect(SourceType::ALPHA_NAME->value)->toBe(1)
        ->and(SourceType::PHONE->value)->toBe(2)
        ->and(SourceType::DOMAIN->value)->toBe(3);
});

test('SourceData ids are stable', function (): void {
    expect(SourceData::TELE2->value)->toBe(4)
        ->and(SourceData::MTS->value)->toBe(5)
        ->and(SourceData::BEELINE->value)->toBe(6)
        ->and(SourceData::ROSTELECOM->value)->toBe(7)
        ->and(SourceData::MEGAFON->value)->toBe(8);
});

test('DayOfWeek matches API spec', function (): void {
    expect(DayOfWeek::SUNDAY->value)->toBe(0)
        ->and(DayOfWeek::SATURDAY->value)->toBe(6);
});

test('ProjectState::fromInput handles whitespace and case', function (string $input, ProjectState $expected): void {
    expect(ProjectState::fromInput($input))->toBe($expected);
})->with([
    [' Active ', ProjectState::ACTIVE],
    ['PAUSED', ProjectState::PAUSED],
    ['active', ProjectState::ACTIVE],
    ['paused', ProjectState::PAUSED],
    ['  ', ProjectState::ACTIVE],
    ['', ProjectState::ACTIVE],
]);

test('ProjectState::fromInput rejects unknown values', function (): void {
    ProjectState::fromInput('disabled');
})->throws(ConfigurationException::class);

test('GeoRegions has expected entries', function (): void {
    expect(GeoRegions::name(0))->toBe('Все регионы')
        ->and(GeoRegions::name(77))->toBe('Москва')
        ->and(GeoRegions::name(78))->toBe('Санкт-Петербург')
        ->and(GeoRegions::name(92))->toBe('Севастополь')
        ->and(GeoRegions::exists(80))->toBeFalse()
        ->and(GeoRegions::exists(83))->toBeTrue();
});

test('GeoRegions throws on unknown id', function (): void {
    GeoRegions::name(999);
})->throws(ConfigurationException::class);

test('SourceType fromName accepts russian and english', function (): void {
    expect(SourceType::fromName('Домен'))->toBe(SourceType::DOMAIN)
        ->and(SourceType::fromName('domain'))->toBe(SourceType::DOMAIN)
        ->and(SourceType::fromName('Альфа имя'))->toBe(SourceType::ALPHA_NAME);
});

test('SourceData fromName accepts known names', function (): void {
    expect(SourceData::fromName('МТС'))->toBe(SourceData::MTS)
        ->and(SourceData::fromName('beeline'))->toBe(SourceData::BEELINE);
});
