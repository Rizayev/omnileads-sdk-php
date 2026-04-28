<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\Response;
use Madtec\OmniLeads\Exceptions\ConfigurationException;

function makePage(int $page, int $totalPages, int $projectsCount = 1): string
{
    $projects = [];
    for ($i = 0; $i < $projectsCount; $i++) {
        $projects[] = [
            'projectId' => sprintf('p-%d-%d', $page, $i),
            'projectName' => sprintf('P%d-%d', $page, $i),
            'segments' => [],
        ];
    }

    return json_encode([
        'date' => '2026-04-08',
        'projects' => $projects,
        'page' => $page,
        'pageSize' => 1,
        'totalCount' => $totalPages,
        'totalPages' => $totalPages,
    ], JSON_THROW_ON_ERROR);
}

test('byDay sends date query without pagination', function (): void {
    $client = $this->buildClient([
        new Response(200, [], json_encode([
            'date' => '2026-04-08',
            'projects' => [],
        ], JSON_THROW_ON_ERROR)),
    ]);

    $data = $client->data()->byDay('2026-04-08');

    expect($data->date)->toBe('2026-04-08')
        ->and($data->page)->toBeNull()
        ->and($data->isPaged())->toBeFalse();

    $query = $this->history[0]['request']->getUri()->getQuery();
    expect($query)->toContain('date=2026-04-08');
});

test('byDay rejects invalid date format', function (): void {
    $client = $this->buildClient([]);

    $client->data()->byDay('2026/04/08');
})->throws(ConfigurationException::class);

test('byDayPaged validates page bounds', function (): void {
    $client = $this->buildClient([]);

    $client->data()->byDayPaged('2026-04-08', page: 0, pageSize: 100);
})->throws(ConfigurationException::class);

test('byDayPaged validates pageSize bounds', function (): void {
    $client = $this->buildClient([]);

    $client->data()->byDayPaged('2026-04-08', page: 1, pageSize: 9999);
})->throws(ConfigurationException::class);

test('iterateByDay walks all pages', function (): void {
    $client = $this->buildClient([
        new Response(200, [], makePage(1, 3)),
        new Response(200, [], makePage(2, 3)),
        new Response(200, [], makePage(3, 3)),
    ]);

    $names = [];
    foreach ($client->data()->iterateByDay('2026-04-08', pageSize: 1) as $project) {
        $names[] = $project->projectName;
    }

    expect($names)->toBe(['P1-0', 'P2-0', 'P3-0'])
        ->and($this->history)->toHaveCount(3);
});
