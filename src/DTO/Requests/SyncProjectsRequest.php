<?php

declare(strict_types=1);

namespace Madtec\OmniLeads\DTO\Requests;

use Madtec\OmniLeads\Exceptions\ConfigurationException;

final readonly class SyncProjectsRequest
{
    /**
     * @param  list<SyncProjectDTO>  $projects
     */
    public function __construct(
        public array $projects,
    ) {
        if ($projects === []) {
            throw new ConfigurationException('SyncProjectsRequest: projects must not be empty');
        }

        foreach ($projects as $project) {
            if (! $project instanceof SyncProjectDTO) {
                throw new ConfigurationException(
                    'SyncProjectsRequest: each project must be a SyncProjectDTO',
                );
            }
        }
    }

    /**
     * @return array{projects: list<array<string, mixed>>}
     */
    public function toArray(): array
    {
        return [
            'projects' => array_map(
                static fn (SyncProjectDTO $project): array => $project->toArray(),
                $this->projects,
            ),
        ];
    }
}
