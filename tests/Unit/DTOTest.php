<?php

declare(strict_types=1);

use Madtec\OmniLeads\DTO\Requests\ChangeStateRequest;
use Madtec\OmniLeads\DTO\Requests\CreateProjectRequest;
use Madtec\OmniLeads\DTO\Requests\CreateSourceRequest;
use Madtec\OmniLeads\DTO\Requests\SyncProjectDTO;
use Madtec\OmniLeads\DTO\Requests\SyncProjectsRequest;
use Madtec\OmniLeads\DTO\Requests\SyncSegmentDTO;
use Madtec\OmniLeads\DTO\Requests\WebhookSettingsRequest;
use Madtec\OmniLeads\DTO\Responses\Project;
use Madtec\OmniLeads\DTO\Responses\ProjectListItem;
use Madtec\OmniLeads\DTO\Responses\ProjectStateResult;
use Madtec\OmniLeads\Enums\DayOfWeek;
use Madtec\OmniLeads\Enums\ProjectState;
use Madtec\OmniLeads\Enums\SourceData;
use Madtec\OmniLeads\Enums\SourceType;
use Madtec\OmniLeads\Exceptions\ConfigurationException;

test('CreateProjectRequest serializes to expected payload', function (): void {
    $dto = new CreateProjectRequest(
        name: 'CRM Import',
        limit: 100,
        limitTypeId: 1,
        description: 'Hello',
        state: ProjectState::ACTIVE,
        workingDaysOfWeek: [DayOfWeek::MONDAY, 5, DayOfWeek::SATURDAY],
        workingDates: ['2026-01-01'],
        excludedDates: ['2026-12-31'],
        geoRegions: [77, 78],
    );

    expect($dto->toArray())->toEqual([
        'name' => 'CRM Import',
        'limit' => 100,
        'limitTypeId' => 1,
        'description' => 'Hello',
        'state' => 'active',
        'workingDaysOfWeek' => [1, 5, 6],
        'workingDates' => ['2026-01-01'],
        'excludedDates' => ['2026-12-31'],
        'geoRegions' => [77, 78],
    ]);
});

test('CreateProjectRequest rejects empty name', function (): void {
    new CreateProjectRequest(name: '   ', limit: 0, limitTypeId: 1);
})->throws(ConfigurationException::class);

test('ChangeStateRequest accepts string and enum', function (): void {
    expect(ChangeStateRequest::fromString(' PAUSED ')->state)->toBe(ProjectState::PAUSED)
        ->and((new ChangeStateRequest(ProjectState::ACTIVE))->toArray())->toEqual(['state' => 'active']);
});

test('CreateSourceRequest requires sourceValue or sourceValues', function (): void {
    new CreateSourceRequest(SourceType::DOMAIN, SourceData::MEGAFON);
})->throws(ConfigurationException::class);

test('CreateSourceRequest serializes correctly', function (): void {
    $dto = new CreateSourceRequest(
        sourceTypeId: SourceType::DOMAIN,
        sourceDataId: SourceData::MEGAFON,
        sourceValues: ['example.com'],
        limit: 1000,
    );

    expect($dto->toArray())->toEqual([
        'sourceTypeId' => 3,
        'sourceDataId' => 8,
        'sourceValues' => ['example.com'],
        'limit' => 1000,
        'projectSourceStatusId' => 1,
    ]);
});

test('WebhookSettingsRequest validates url when enabled', function (): void {
    new WebhookSettingsRequest(isEnabled: true, webhookUrl: null);
})->throws(ConfigurationException::class);

test('SyncSegmentDTO uses string names for sourceType/sourceData', function (): void {
    $segment = SyncSegmentDTO::fromEnums(
        SourceType::DOMAIN,
        SourceData::MEGAFON,
        ['example.com'],
        500,
    );

    expect($segment->toArray())->toEqual([
        'sourceType' => 'Домен',
        'sourceData' => 'Мегафон',
        'sourceValues' => ['example.com'],
        'limit' => 500,
    ]);
});

test('SyncProjectsRequest serializes nested DTOs', function (): void {
    $request = new SyncProjectsRequest([
        new SyncProjectDTO(
            name: 'Test',
            limit: 10,
            limitType: 'Дневной',
            segments: [
                new SyncSegmentDTO('Домен', 'МТС', ['a.com'], 5),
            ],
        ),
    ]);

    $payload = $request->toArray();

    expect($payload['projects'][0]['name'])->toBe('Test')
        ->and($payload['projects'][0]['segments'][0]['sourceData'])->toBe('МТС');
});

test('Project response DTO round-trips', function (): void {
    $payload = [
        'id' => 'a1b2c3d4-1234-5678-9abc-1234567890ab',
        'name' => 'Project',
        'description' => null,
        'limit' => 10,
        'limitTypeId' => 2,
        'limitTypeName' => 'Daily',
        'createdAt' => '2026-03-23T10:12:45Z',
        'updatedAt' => null,
        'isActive' => true,
        'userId' => 'u-1',
        'userEmail' => 'user@example.com',
        'userFirstName' => null,
        'userLastName' => null,
        'workingDaysOfWeek' => [1, 2, 3],
        'workingDates' => [],
        'excludedDates' => [],
        'geoRegions' => [77],
        'projectSources' => [],
    ];

    $project = Project::fromArray($payload);

    expect($project->id)->toBe($payload['id'])
        ->and($project->workingDaysOfWeek)->toBe([1, 2, 3])
        ->and($project->createdAt->format('Y-m-d'))->toBe('2026-03-23')
        ->and($project->createdAt->getTimezone()->getName())->toBe('UTC');
});

test('ProjectListItem fromArray handles missing fields gracefully', function (): void {
    $item = ProjectListItem::fromArray([
        'id' => 'guid-1',
        'name' => 'P',
        'limit' => 0,
        'limitTypeId' => 0,
        'limitTypeName' => '',
        'createdAt' => '2026-01-01T00:00:00Z',
        'isActive' => false,
        'sourcesCount' => 0,
    ]);

    expect($item->sourceValues)->toBe([])
        ->and($item->updatedAt)->toBeNull();
});

test('ProjectStateResult parses state strings', function (): void {
    $result = ProjectStateResult::fromArray([
        'projectId' => 'guid',
        'state' => 'PAUSED',
        'isActive' => false,
        'changed' => true,
        'message' => 'OK',
    ]);

    expect($result->state)->toBe(ProjectState::PAUSED)
        ->and($result->changed)->toBeTrue();
});
