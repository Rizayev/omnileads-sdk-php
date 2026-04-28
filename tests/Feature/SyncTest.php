<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\Response;
use Madtec\OmniLeads\DTO\Requests\SyncProjectDTO;
use Madtec\OmniLeads\DTO\Requests\SyncProjectsRequest;
use Madtec\OmniLeads\DTO\Requests\SyncSegmentDTO;
use Madtec\OmniLeads\Enums\SourceData;
use Madtec\OmniLeads\Enums\SourceType;

test('sync push sends names not ids', function (): void {
    $client = $this->buildClient([
        new Response(200, [], json_encode([
            'success' => true,
            'summary' => [
                'projects' => ['created' => 1, 'updated' => 0, 'disabled' => 0],
                'segments' => ['created' => 1, 'updated' => 0, 'disabled' => 0],
            ],
            'errors' => [],
        ], JSON_THROW_ON_ERROR)),
    ]);

    $request = new SyncProjectsRequest([
        new SyncProjectDTO(
            name: 'Sync test',
            limit: 10,
            limitType: 'Дневной',
            segments: [
                SyncSegmentDTO::fromEnums(SourceType::DOMAIN, SourceData::MEGAFON, ['a.com'], 5),
            ],
        ),
    ]);

    $result = $client->sync()->push($request);

    expect($result->success)->toBeTrue()
        ->and($result->projects['created'])->toBe(1);

    $sent = json_decode((string) $this->history[0]['request']->getBody(), true);
    expect($sent['projects'][0]['limitType'])->toBe('Дневной')
        ->and($sent['projects'][0]['segments'][0]['sourceType'])->toBe('Домен')
        ->and($sent['projects'][0]['segments'][0]['sourceData'])->toBe('Мегафон');
});

test('sync pull returns raw payload', function (): void {
    $client = $this->buildClient([
        new Response(200, [], json_encode([
            'projects' => [
                ['name' => 'P', 'limit' => 1, 'limitType' => 'X', 'segments' => []],
            ],
        ], JSON_THROW_ON_ERROR)),
    ]);

    $payload = $client->sync()->pull();

    expect($payload['projects'][0]['name'])->toBe('P');
});
