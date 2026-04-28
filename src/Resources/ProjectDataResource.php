<?php

declare(strict_types=1);

namespace Madtec\OmniLeads\Resources;

use Generator;
use Madtec\OmniLeads\DTO\Responses\DayProject;
use Madtec\OmniLeads\DTO\Responses\ProjectDayData;
use Madtec\OmniLeads\Exceptions\ConfigurationException;
use Madtec\OmniLeads\Http\AuthType;
use Madtec\OmniLeads\Support\DateParser;
use Madtec\OmniLeads\Support\GuidValidator;

final class ProjectDataResource extends Resource
{
    private const MAX_PAGE_SIZE = 5000;

    private const DEFAULT_PAGE_SIZE = 1000;

    public function byDay(string $date, ?string $projectId = null): ProjectDayData
    {
        DateParser::assertIsoDate($date, 'date');

        $query = ['date' => $date];

        if ($projectId !== null) {
            GuidValidator::assert($projectId, 'projectId');
            $query['projectId'] = $projectId;
        }

        $options = $this->factory->buildOptions(AuthType::API_KEY, query: $query);
        $response = $this->http->request('GET', '/Project/data/by-day', $options);

        return ProjectDayData::fromArray($response);
    }

    public function byDayPaged(
        string $date,
        int $page = 1,
        int $pageSize = self::DEFAULT_PAGE_SIZE,
        ?string $projectId = null,
    ): ProjectDayData {
        DateParser::assertIsoDate($date, 'date');
        $this->assertPagination($page, $pageSize);

        $query = [
            'date' => $date,
            'page' => $page,
            'pageSize' => $pageSize,
        ];

        if ($projectId !== null) {
            GuidValidator::assert($projectId, 'projectId');
            $query['projectId'] = $projectId;
        }

        $options = $this->factory->buildOptions(AuthType::API_KEY, query: $query);
        $response = $this->http->request('GET', '/Project/data/by-day', $options);

        return ProjectDayData::fromArray($response);
    }

    /**
     * Yield each project across all pages.
     *
     * @return Generator<int, DayProject>
     */
    public function iterateByDay(
        string $date,
        ?string $projectId = null,
        int $pageSize = self::DEFAULT_PAGE_SIZE,
    ): Generator {
        DateParser::assertIsoDate($date, 'date');

        $page = 1;
        while (true) {
            $result = $this->byDayPaged($date, $page, $pageSize, $projectId);

            foreach ($result->projects as $project) {
                yield $project;
            }

            $totalPages = $result->totalPages ?? 1;
            if ($page >= $totalPages) {
                break;
            }

            $page++;
        }
    }

    private function assertPagination(int $page, int $pageSize): void
    {
        if ($page < 1) {
            throw new ConfigurationException('page must be >= 1');
        }

        if ($pageSize < 1 || $pageSize > self::MAX_PAGE_SIZE) {
            throw new ConfigurationException(
                sprintf('pageSize must be between 1 and %d', self::MAX_PAGE_SIZE),
            );
        }
    }
}
