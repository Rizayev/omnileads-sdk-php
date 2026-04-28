<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\Response;
use Madtec\OmniLeads\DTO\Requests\CreateSourceRequest;
use Madtec\OmniLeads\DTO\Requests\UpdateSourceRequest;
use Madtec\OmniLeads\Enums\SourceData;
use Madtec\OmniLeads\Enums\SourceType;

test('add source posts payload', function (): void {
    $client = $this->buildClient([
        new Response(200, [], json_encode([
            'id' => 'src-1',
            'message' => 'Created',
        ], JSON_THROW_ON_ERROR)),
    ]);

    $result = $client->sources()->add(
        'a1b2c3d4-1234-5678-9abc-1234567890ab',
        new CreateSourceRequest(
            sourceTypeId: SourceType::DOMAIN,
            sourceDataId: SourceData::MEGAFON,
            sourceValues: ['example.com'],
            limit: 100,
        ),
    );

    expect($result->id)->toBe('src-1');

    $sent = json_decode((string) $this->history[0]['request']->getBody(), true);
    expect($sent['sourceTypeId'])->toBe(3)
        ->and($sent['sourceDataId'])->toBe(8)
        ->and($sent['sourceValues'])->toBe(['example.com']);
});

test('update source returns ProjectSource', function (): void {
    $body = json_encode([
        'id' => 'src-1',
        'sourceTypeId' => 3,
        'sourceTypeName' => 'Домен',
        'sourceDataId' => 8,
        'sourceDataName' => 'Мегафон',
        'projectSourceStatusId' => 1,
        'projectSourceStatusName' => 'Активный',
        'sourceValues' => ['example.com'],
        'limit' => 100,
        'createdAt' => '2026-01-01T00:00:00Z',
    ], JSON_THROW_ON_ERROR);

    $client = $this->buildClient([
        new Response(200, [], $body),
    ]);

    $source = $client->sources()->update(
        'a1b2c3d4-1234-5678-9abc-1234567890ab',
        'src-1',
        new UpdateSourceRequest(limit: 100),
    );

    expect($source->id)->toBe('src-1')
        ->and($source->sourceValues)->toBe(['example.com']);
});

test('delete returns true on 204', function (): void {
    $client = $this->buildClient([
        new Response(204, [], ''),
    ]);

    expect($client->sources()->delete('a1b2c3d4-1234-5678-9abc-1234567890ab', 'src-1'))->toBeTrue();
});
