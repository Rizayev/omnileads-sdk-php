<?php

declare(strict_types=1);

namespace Madtec\OmniLeads\Resources;

use Madtec\OmniLeads\DTO\Requests\ChangeStateRequest;
use Madtec\OmniLeads\DTO\Requests\CreateProjectRequest;
use Madtec\OmniLeads\DTO\Requests\UpdateProjectRequest;
use Madtec\OmniLeads\DTO\Responses\CreateProjectResult;
use Madtec\OmniLeads\DTO\Responses\Project;
use Madtec\OmniLeads\DTO\Responses\ProjectListItem;
use Madtec\OmniLeads\DTO\Responses\ProjectStateResult;
use Madtec\OmniLeads\Enums\ProjectState;
use Madtec\OmniLeads\Http\AuthType;
use Madtec\OmniLeads\Support\GuidValidator;

final class ProjectResource extends Resource
{
    /**
     * @return list<ProjectListItem>
     */
    public function list(): array
    {
        $options = $this->factory->buildOptions(AuthType::API_KEY);
        $response = $this->http->request('GET', '/Project', $options);

        $items = [];
        foreach ($response as $row) {
            if (is_array($row)) {
                $items[] = ProjectListItem::fromArray($row);
            }
        }

        return $items;
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
