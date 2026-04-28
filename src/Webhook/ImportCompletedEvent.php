<?php

declare(strict_types=1);

namespace Madtec\OmniLeads\Webhook;

use DateTimeImmutable;
use Madtec\OmniLeads\DTO\Responses\DayProject;
use Madtec\OmniLeads\Support\DateParser;

final readonly class ImportCompletedEvent
{
    /**
     * @param  list<DayProject>  $projects
     */
    public function __construct(
        public string $eventType,
        public DateTimeImmutable $occurredAt,
        public string $userId,
        public ?string $userEmail,
        public string $importType,
        public int $totalProjects,
        public int $totalSegments,
        public int $totalPhones,
        public array $projects,
    ) {}

    /**
     * @param  array<int|string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $projects = [];
        if (isset($data['projects']) && is_array($data['projects'])) {
            foreach ($data['projects'] as $row) {
                if (is_array($row)) {
                    $projects[] = DayProject::fromArray($row);
                }
            }
        }

        $occurredAt = DateParser::parse($data['occurredAt'] ?? null) ?? new DateTimeImmutable;

        $userEmailRaw = $data['userEmail'] ?? null;
        $userEmail = is_string($userEmailRaw) && $userEmailRaw !== '' ? $userEmailRaw : null;

        return new self(
            eventType: (string) ($data['eventType'] ?? ''),
            occurredAt: $occurredAt,
            userId: (string) ($data['userId'] ?? ''),
            userEmail: $userEmail,
            importType: (string) ($data['importType'] ?? ''),
            totalProjects: (int) ($data['totalProjects'] ?? 0),
            totalSegments: (int) ($data['totalSegments'] ?? 0),
            totalPhones: (int) ($data['totalPhones'] ?? 0),
            projects: $projects,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'eventType' => $this->eventType,
            'occurredAt' => DateParser::format($this->occurredAt),
            'userId' => $this->userId,
            'userEmail' => $this->userEmail,
            'importType' => $this->importType,
            'totalProjects' => $this->totalProjects,
            'totalSegments' => $this->totalSegments,
            'totalPhones' => $this->totalPhones,
            'projects' => array_map(
                static fn (DayProject $project): array => $project->toArray(),
                $this->projects,
            ),
        ];
    }
}
