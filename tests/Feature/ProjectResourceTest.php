<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\Response;
use Madtec\OmniLeads\Config\RetryConfig;
use Madtec\OmniLeads\DTO\Requests\CreateProjectRequest;
use Madtec\OmniLeads\DTO\Requests\UpdateProjectRequest;
use Madtec\OmniLeads\Enums\ProjectState;
use Madtec\OmniLeads\Exceptions\AuthenticationException;
use Madtec\OmniLeads\Exceptions\ConfigurationException;
use Madtec\OmniLeads\Exceptions\NotFoundException;
use Madtec\OmniLeads\Exceptions\ServerException;
use Madtec\OmniLeads\Exceptions\ValidationException;

test('list unwraps paginated items', function (): void {
    $body = json_encode([
        'items' => [
            [
                'id' => 'a1b2c3d4-1234-5678-9abc-1234567890ab',
                'name' => 'P1',
                'limit' => 100,
                'limitTypeId' => 1,
                'limitTypeName' => 'Daily',
                'createdAt' => '2026-03-23T10:12:45Z',
                'isActive' => true,
                'sourcesCount' => 1,
                'sourceValues' => ['a.com'],
            ],
        ],
        'page' => 1,
        'pageSize' => 100,
        'totalCount' => 1,
        'totalPages' => 1,
        'hasPreviousPage' => false,
        'hasNextPage' => false,
    ], JSON_THROW_ON_ERROR);

    $client = $this->buildClient([
        new Response(200, ['Content-Type' => 'application/json'], $body),
    ]);

    $items = $client->projects()->list();

    expect($items)->toHaveCount(1)
        ->and($items[0]->name)->toBe('P1')
        ->and($items[0]->sourceValues)->toBe(['a.com']);

    expect((string) $this->history[0]['request']->getUri())->toContain('/Project');
    expect($this->history[0]['request']->getHeaderLine('X-Api-Key'))->toBe('test-api-key');
});

test('listPaged returns PagedResult with metadata', function (): void {
    $body = json_encode([
        'items' => [],
        'page' => 2,
        'pageSize' => 50,
        'totalCount' => 120,
        'totalPages' => 3,
        'hasPreviousPage' => true,
        'hasNextPage' => true,
    ], JSON_THROW_ON_ERROR);

    $client = $this->buildClient([
        new Response(200, [], $body),
    ]);

    $page = $client->projects()->listPaged(page: 2, pageSize: 50);

    expect($page->page)->toBe(2)
        ->and($page->totalPages)->toBe(3)
        ->and($page->totalCount)->toBe(120)
        ->and($page->hasNextPage())->toBeTrue();
});

test('iterateAll walks all pages of /Project', function (): void {
    $page1 = json_encode([
        'items' => [
            ['id' => 'aaaaaaaa-1111-1111-1111-111111111111', 'name' => 'A', 'limit' => 0, 'limitTypeId' => 1, 'limitTypeName' => '', 'createdAt' => '2026-01-01T00:00:00Z', 'isActive' => true, 'sourcesCount' => 0],
        ],
        'page' => 1, 'pageSize' => 1, 'totalCount' => 2, 'totalPages' => 2, 'hasPreviousPage' => false, 'hasNextPage' => true,
    ], JSON_THROW_ON_ERROR);

    $page2 = json_encode([
        'items' => [
            ['id' => 'bbbbbbbb-2222-2222-2222-222222222222', 'name' => 'B', 'limit' => 0, 'limitTypeId' => 1, 'limitTypeName' => '', 'createdAt' => '2026-01-01T00:00:00Z', 'isActive' => true, 'sourcesCount' => 0],
        ],
        'page' => 2, 'pageSize' => 1, 'totalCount' => 2, 'totalPages' => 2, 'hasPreviousPage' => true, 'hasNextPage' => false,
    ], JSON_THROW_ON_ERROR);

    $client = $this->buildClient([
        new Response(200, [], $page1),
        new Response(200, [], $page2),
    ]);

    $names = [];
    foreach ($client->projects()->iterateAll(pageSize: 1) as $item) {
        $names[] = $item->name;
    }

    expect($names)->toBe(['A', 'B']);
});

test('create project sends payload and returns id', function (): void {
    $client = $this->buildClient([
        new Response(201, [], json_encode([
            'id' => 'a1b2c3d4-1234-5678-9abc-1234567890ab',
            'message' => 'Created',
        ], JSON_THROW_ON_ERROR)),
    ]);

    $result = $client->projects()->create(new CreateProjectRequest(
        name: 'New',
        limit: 100,
        limitTypeId: 1,
        state: ProjectState::ACTIVE,
    ));

    expect($result->id)->toBe('a1b2c3d4-1234-5678-9abc-1234567890ab');

    $sent = json_decode((string) $this->history[0]['request']->getBody(), true);
    expect($sent['state'])->toBe('active');
});

test('get rejects invalid GUID', function (): void {
    $client = $this->buildClient([]);

    $client->projects()->get('not-a-guid');
})->throws(ConfigurationException::class);

test('changeState is idempotent', function (): void {
    $first = json_encode([
        'projectId' => 'guid',
        'state' => 'paused',
        'isActive' => false,
        'changed' => true,
        'message' => 'OK',
    ], JSON_THROW_ON_ERROR);

    $second = json_encode([
        'projectId' => 'guid',
        'state' => 'paused',
        'isActive' => false,
        'changed' => false,
        'message' => 'No change',
    ], JSON_THROW_ON_ERROR);

    $client = $this->buildClient([
        new Response(200, [], $first),
        new Response(200, [], $second),
    ]);

    $r1 = $client->projects()->changeState('a1b2c3d4-1234-5678-9abc-1234567890ab', 'PAUSED');
    $r2 = $client->projects()->changeState('a1b2c3d4-1234-5678-9abc-1234567890ab', ProjectState::PAUSED);

    expect($r1->changed)->toBeTrue()
        ->and($r2->changed)->toBeFalse()
        ->and($r2->state)->toBe(ProjectState::PAUSED);
});

test('update sends partial body', function (): void {
    $client = $this->buildClient([
        new Response(200, [], json_encode([
            'id' => 'a1b2c3d4-1234-5678-9abc-1234567890ab',
            'name' => 'Updated',
            'limit' => 50,
            'limitTypeId' => 1,
            'limitTypeName' => 'Daily',
            'createdAt' => '2026-01-01T00:00:00Z',
            'isActive' => true,
            'userId' => 'u',
            'projectSources' => [],
        ], JSON_THROW_ON_ERROR)),
    ]);

    $project = $client->projects()->update(
        'a1b2c3d4-1234-5678-9abc-1234567890ab',
        new UpdateProjectRequest(name: 'Updated'),
    );

    expect($project->name)->toBe('Updated');

    $sent = json_decode((string) $this->history[0]['request']->getBody(), true);
    expect($sent)->toEqual(['name' => 'Updated']);
});

test('400 response throws ValidationException', function (): void {
    $client = $this->buildClient([
        new Response(400, [], json_encode(['message' => 'Bad'], JSON_THROW_ON_ERROR)),
    ]);

    $client->projects()->list();
})->throws(ValidationException::class);

test('401 response throws AuthenticationException', function (): void {
    $client = $this->buildClient([
        new Response(401, [], json_encode(['message' => 'Unauthorized'], JSON_THROW_ON_ERROR)),
    ]);

    $client->projects()->list();
})->throws(AuthenticationException::class);

test('404 response throws NotFoundException', function (): void {
    $client = $this->buildClient([
        new Response(404, [], json_encode(['message' => 'Not found'], JSON_THROW_ON_ERROR)),
    ]);

    $client->projects()->get('a1b2c3d4-1234-5678-9abc-1234567890ab');
})->throws(NotFoundException::class);

test('500 response throws ServerException without retries when retry disabled', function (): void {
    $client = $this->buildClient([
        new Response(500, [], 'oops'),
    ]);

    $client->projects()->list();
})->throws(ServerException::class);

test('5xx triggers retry when enabled', function (): void {
    $client = $this->buildClient([
        new Response(503, [], 'busy'),
        new Response(200, [], json_encode([], JSON_THROW_ON_ERROR)),
    ], [
        'retry' => new RetryConfig(true, 2, 0),
    ]);

    $items = $client->projects()->list();

    expect($items)->toBe([])
        ->and($this->history)->toHaveCount(2);
});

test('changeState routes through activate helper', function (): void {
    $client = $this->buildClient([
        new Response(200, [], json_encode([
            'projectId' => 'guid',
            'state' => 'active',
            'isActive' => true,
            'changed' => true,
            'message' => 'OK',
        ], JSON_THROW_ON_ERROR)),
    ]);

    $client->projects()->activate('a1b2c3d4-1234-5678-9abc-1234567890ab');

    $sent = json_decode((string) $this->history[0]['request']->getBody(), true);
    expect($sent)->toEqual(['state' => 'active']);
});
