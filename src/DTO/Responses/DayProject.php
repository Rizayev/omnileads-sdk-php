<?php

declare(strict_types=1);

namespace Madtec\OmniLeads\DTO\Responses;

final readonly class DayProject
{
    /**
     * @param  list<ProjectSegment>  $segments
     */
    public function __construct(
        public string $projectId,
        public string $projectName,
        public array $segments,
    ) {}

    /**
     * @param  array<int|string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $segments = [];
        if (isset($data['segments']) && is_array($data['segments'])) {
            foreach ($data['segments'] as $row) {
                if (is_array($row)) {
                    $segments[] = ProjectSegment::fromArray($row);
                }
            }
        }

        return new self(
            projectId: (string) ($data['projectId'] ?? ''),
            projectName: (string) ($data['projectName'] ?? ''),
            segments: $segments,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'projectId' => $this->projectId,
            'projectName' => $this->projectName,
            'segments' => array_map(
                static fn (ProjectSegment $segment): array => $segment->toArray(),
                $this->segments,
            ),
        ];
    }
}
