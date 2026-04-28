<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\Response;
use Madtec\OmniLeads\DTO\Requests\SyncProjectDTO;
use Madtec\OmniLeads\DTO\Requests\SyncProjectsRequest;
use Madtec\OmniLeads\DTO\Requests\SyncSegmentDTO;
use Madtec\OmniLeads\DTO\Requests\UpdateSourceRequest;
use Madtec\OmniLeads\Enums\SourceData;
use Madtec\OmniLeads\Enums\SourceType;
use Madtec\OmniLeads\Exceptions\ApiException;
use Madtec\OmniLeads\Exceptions\ServerException;
use Madtec\OmniLeads\Exceptions\ValidationException;
use Madtec\OmniLeads\Http\AuthType;
use Madtec\OmniLeads\Webhook\WebhookPayloadParser;

/*
 * These tests pin behaviours we observed by smoke-testing the real OmniLeads
 * production API. They are not invented edge cases — each one corresponds to
 * a response the API actually returned during integration testing.
 */

test('PUT source with limit on a balanced project raises ValidationException with API message', function (): void {
    $apiBody = json_encode([
        'message' => 'Редактирование лимита источника запрещено для выбранного типа лимита проекта',
    ], JSON_THROW_ON_ERROR);

    $client = $this->buildClient([
        new Response(400, ['Content-Type' => 'application/json'], $apiBody),
    ]);

    try {
        $client->sources()->update(
            'a1b2c3d4-1234-5678-9abc-1234567890ab',
            '17516',
            new UpdateSourceRequest(
                sourceTypeId: SourceType::PHONE,
                sourceDataId: SourceData::MEGAFON,
                limit: 40,
            ),
        );
        expect(false)->toBeTrue('Expected ValidationException');
    } catch (ValidationException $e) {
        expect($e->statusCode)->toBe(400)
            ->and($e->errorMessage)->toBe(
                'Редактирование лимита источника запрещено для выбранного типа лимита проекта',
            );
    }
});

test('POST /Project/sync 500 maps to ServerException with API message', function (): void {
    $apiBody = json_encode([
        'message' => 'Внутренняя ошибка сервера',
    ], JSON_THROW_ON_ERROR);

    $client = $this->buildClient([
        new Response(500, ['Content-Type' => 'application/json'], $apiBody),
    ]);

    try {
        $client->sync()->push(new SyncProjectsRequest([
            new SyncProjectDTO(
                name: 'Test',
                limit: 100,
                limitType: 'Ручной',
                segments: [
                    SyncSegmentDTO::fromEnums(SourceType::DOMAIN, SourceData::MEGAFON, ['example.com']),
                ],
            ),
        ]));
        expect(false)->toBeTrue('Expected ServerException');
    } catch (ServerException $e) {
        expect($e->statusCode)->toBe(500)
            ->and($e->errorMessage)->toBe('Внутренняя ошибка сервера');
    }
});

test('DELETE /Project returns 405 -> generic ApiException', function (): void {
    $client = $this->buildClient([
        new Response(405, [], ''),
    ]);

    try {
        $client->http()->request(
            'DELETE',
            '/Project/a1b2c3d4-1234-5678-9abc-1234567890ab',
            $client->factory()->buildOptions(AuthType::API_KEY),
        );
        expect(false)->toBeTrue('Expected ApiException');
    } catch (ApiException $e) {
        expect($e::class)->toBe(ApiException::class)
            ->and($e->statusCode)->toBe(405);
    }
});

test('GET /Project pagination wrapper has all real API fields', function (): void {
    $body = json_encode([
        'items' => [],
        'page' => 1,
        'pageSize' => 10,
        'totalCount' => 0,
        'totalPages' => 0,
        'hasPreviousPage' => false,
        'hasNextPage' => false,
    ], JSON_THROW_ON_ERROR);

    $client = $this->buildClient([
        new Response(200, [], $body),
    ]);

    $page = $client->projects()->listPaged();

    expect($page->items)->toBe([])
        ->and($page->page)->toBe(1)
        ->and($page->pageSize)->toBe(10)
        ->and($page->totalCount)->toBe(0)
        ->and($page->totalPages)->toBe(0)
        ->and($page->hasNextPage())->toBeFalse();
});

test('source response with numeric id is normalized to string', function (): void {
    $body = json_encode([
        'id' => 17515,
        'sourceTypeId' => 3,
        'sourceTypeName' => 'Домен',
        'sourceDataId' => 8,
        'sourceDataName' => 'Мегафон',
        'projectSourceStatusId' => 1,
        'projectSourceStatusName' => 'Активный',
        'sourceValues' => ['megafon-test.example.com'],
        'limit' => 50,
        'createdAt' => '2026-04-28T08:00:00Z',
    ], JSON_THROW_ON_ERROR);

    $client = $this->buildClient([
        new Response(200, [], $body),
    ]);

    $source = $client->sources()->update(
        'a1b2c3d4-1234-5678-9abc-1234567890ab',
        '17515',
        new UpdateSourceRequest,
    );

    expect($source->id)->toBe('17515')
        ->and($source->sourceTypeName)->toBe('Домен')
        ->and($source->sourceDataName)->toBe('Мегафон');
});

test('webhook payload phone arrives as numeric and is normalized to string', function (): void {
    $parser = new WebhookPayloadParser;

    $event = $parser->parse(json_encode([
        'eventType' => 'import.completed',
        'occurredAt' => '2026-03-23T10:12:45Z',
        'userId' => 'user-1',
        'importType' => 'phones',
        'totalProjects' => 1,
        'totalSegments' => 1,
        'totalPhones' => 1,
        'projects' => [[
            'projectId' => 'p',
            'projectName' => 'CRM',
            'segments' => [[
                'projectSourceId' => 145,
                'sourceTypeId' => 3,
                'sourceTypeName' => 'Домен',
                'sourceDataId' => 8,
                'sourceDataName' => 'Мегафон',
                'sourceValues' => ['example.com'],
                'phones' => [[
                    'phone' => 79001234567,
                    'createdAt' => '2026-03-23T10:12:40Z',
                ]],
            ]],
        ]],
    ], JSON_THROW_ON_ERROR));

    $phone = $event->projects[0]->segments[0]->phones[0];

    expect($phone->phone)->toBe('79001234567')
        ->and($phone->createdAt->format(DATE_ATOM))->toBe('2026-03-23T10:12:40+00:00');
});
