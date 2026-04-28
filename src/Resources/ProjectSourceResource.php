<?php

declare(strict_types=1);

namespace Madtec\OmniLeads\Resources;

use Madtec\OmniLeads\DTO\Requests\CreateSourceRequest;
use Madtec\OmniLeads\DTO\Requests\UpdateSourceRequest;
use Madtec\OmniLeads\DTO\Responses\CreateSourceResult;
use Madtec\OmniLeads\DTO\Responses\ProjectSource;
use Madtec\OmniLeads\Exceptions\ConfigurationException;
use Madtec\OmniLeads\Http\AuthType;
use Madtec\OmniLeads\Support\GuidValidator;

final class ProjectSourceResource extends Resource
{
    public function add(string $projectId, CreateSourceRequest $request): CreateSourceResult
    {
        GuidValidator::assert($projectId, 'projectId');

        $options = $this->factory->buildOptions(AuthType::API_KEY, body: $request->toArray());
        $response = $this->http->request('POST', '/Project/'.$projectId.'/sources', $options);

        return CreateSourceResult::fromArray($response);
    }

    public function update(string $projectId, string $sourceId, UpdateSourceRequest $request): ProjectSource
    {
        GuidValidator::assert($projectId, 'projectId');
        $this->assertSourceId($sourceId);

        $options = $this->factory->buildOptions(AuthType::API_KEY, body: $request->toArray());
        $response = $this->http->request(
            'PUT',
            '/Project/'.$projectId.'/sources/'.rawurlencode($sourceId),
            $options,
        );

        return ProjectSource::fromArray($response);
    }

    public function delete(string $projectId, string $sourceId): bool
    {
        GuidValidator::assert($projectId, 'projectId');
        $this->assertSourceId($sourceId);

        $options = $this->factory->buildOptions(AuthType::API_KEY);
        $this->http->request(
            'DELETE',
            '/Project/'.$projectId.'/sources/'.rawurlencode($sourceId),
            $options,
        );

        return true;
    }

    private function assertSourceId(string $sourceId): void
    {
        if (trim($sourceId) === '') {
            throw new ConfigurationException('sourceId must not be empty');
        }
    }
}
