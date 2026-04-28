<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\Response;
use Madtec\OmniLeads\Enums\GeoRegions;

test('limit types are listed', function (): void {
    $client = $this->buildClient([
        new Response(200, [], json_encode([
            ['id' => 1, 'name' => 'Дневной'],
            ['id' => 2, 'name' => 'Недельный'],
        ], JSON_THROW_ON_ERROR)),
    ]);

    $items = $client->limitTypes()->all();

    expect($items)->toHaveCount(2)
        ->and($items[0]->name)->toBe('Дневной');
});

test('geo regions builtin works without HTTP', function (): void {
    $client = $this->buildClient([]);

    $regions = $client->geoRegions()->builtin();

    expect($regions)->toHaveCount(count(GeoRegions::all()));
});

test('geo regions remote endpoint returns API list', function (): void {
    $client = $this->buildClient([
        new Response(200, [], json_encode([
            ['id' => 77, 'name' => 'Москва'],
        ], JSON_THROW_ON_ERROR)),
    ]);

    $regions = $client->geoRegions()->all();

    expect($regions)->toHaveCount(1)
        ->and($regions[0]->name)->toBe('Москва');
});
