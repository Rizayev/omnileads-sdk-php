<?php

declare(strict_types=1);

namespace Madtec\OmniLeads\Resources;

use Generator;
use Madtec\OmniLeads\DTO\Requests\ChangeStateRequest;
use Madtec\OmniLeads\DTO\Requests\CreateProjectRequest;
use Madtec\OmniLeads\DTO\Requests\UpdateProjectRequest;
use Madtec\OmniLeads\DTO\Responses\CreateProjectResult;
use Madtec\OmniLeads\DTO\Responses\PagedResult;
use Madtec\OmniLeads\DTO\Responses\Project;
use Madtec\OmniLeads\DTO\Responses\ProjectListItem;
use Madtec\OmniLeads\DTO\Responses\ProjectStateResult;
use Madtec\OmniLeads\Enums\ProjectState;
use Madtec\OmniLeads\Exceptions\ConfigurationException;
use Madtec\OmniLeads\Http\AuthType;
use Madtec\OmniLeads\Support\GuidValidator;

final class ProjectResource extends Resource
{
    private const MAX_PAGE_SIZE = 5000;

    private const DEFAULT_PAGE_SIZE = 100;

    /**
     * Return the first page of projects.
     *
     * The OmniLeads API paginates this endpoint and wraps results in
     * `{items, page, pageSize, totalCount, totalPages, ...}`. This helper
     * unwraps `items` for the common case of "just give me the projects".
     *
     * Use `listPaged()` for explicit page/pageSize control or `iterateAll()`
     * to walk every page lazily.
     *
     * @return list<ProjectListItem>
     */
    public function list(?int $pageSize = null): array
    {
        return $this->listPaged(page: 1, pageSize: $pageSize ?? self::DEFAULT_PAGE_SIZE)->items;
    }

    /**
     * @return PagedResult<ProjectListItem>
     */
    public function listPaged(int $page = 1, int $pageSize = self::DEFAULT_PAGE_SIZE): PagedResult
    {
        $this->assertPagination($page, $pageSize);

        $options = $this->factory->buildOptions(AuthType::API_KEY, query: [
            'page' => $page,
            'pageSize' => $pageSize,
        ]);

        $response = $this->http->request('GET', '/Project', $options);

        return PagedResult::fromArray(
            $response,
            static fn (array $row): ProjectListItem => ProjectListItem::fromArray($row),
            'items',
        );
    }

    /**
     * @return Generator<int, ProjectListItem>
     */
    public function iterateAll(int $pageSize = self::DEFAULT_PAGE_SIZE): Generator
    {
        $page = 1;
        while (true) {
            $result = $this->listPaged($page, $pageSize);
            foreach ($result->items as $item) {
                yield $item;
            }

            if (! $result->hasNextPage()) {
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

    public function get(string $projectId): Project
    {
        GuidValidator::assert($projectId, 'projectId');

        $options = $this->factory->buildOptions(AuthType::API_KEY);
        $response = $this->http->request('GET', '/Project/'.$projectId, $options);

        return Project::fromArray($response);
    }

    public function create(CreateProjectRequest $request): CreateProjectResult
    {
        $options = $this->factory->buildOptions(AuthType::API_KEY, body: $request->toArray());
        $response = $this->http->request('POST', '/Project', $options);

        return CreateProjectResult::fromArray($response);
    }

    public function update(string $projectId, UpdateProjectRequest $request): Project
    {
        GuidValidator::assert($projectId, 'projectId');

        $options = $this->factory->buildOptions(AuthType::API_KEY, body: $request->toArray());
        $response = $this->http->request('PUT', '/Project/'.$projectId, $options);

        return Project::fromArray($response);
    }

    public function changeState(string $projectId, ChangeStateRequest|ProjectState|string $state): ProjectStateResult
    {
        GuidValidator::assert($projectId, 'projectId');

        $request = match (true) {
            $state instanceof ChangeStateRequest => $state,
            $state instanceof ProjectState => new ChangeStateRequest($state),
            default => ChangeStateRequest::fromString($state),
        };

        $options = $this->factory->buildOptions(AuthType::API_KEY, body: $request->toArray());
        $response = $this->http->request('PATCH', '/Project/'.$projectId.'/state', $options);

        return ProjectStateResult::fromArray($response);
    }

    public function activate(string $projectId): ProjectStateResult
    {
        return $this->changeState($projectId, ProjectState::ACTIVE);
    }

    public function pause(string $projectId): ProjectStateResult
    {
        return $this->changeState($projectId, ProjectState::PAUSED);
    }
}
