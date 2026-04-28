<?php

declare(strict_types=1);

namespace Madtec\OmniLeads\DTO\Responses;

final readonly class ProjectDayData
{
    /**
     * @param  list<DayProject>  $projects
     */
    public function __construct(
        public string $date,
        public array $projects,
        public ?int $page,
        public ?int $pageSize,
        public ?int $totalCount,
        public ?int $totalPages,
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

        return new self(
            date: (string) ($data['date'] ?? ''),
            projects: $projects,
            page: isset($data['page']) ? (int) $data['page'] : null,
            pageSize: isset($data['pageSize']) ? (int) $data['pageSize'] : null,
            totalCount: isset($data['totalCount']) ? (int) $data['totalCount'] : null,
            totalPages: isset($data['totalPages']) ? (int) $data['totalPages'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'date' => $this->date,
            'projects' => array_map(
                static fn (DayProject $project): array => $project->toArray(),
                $this->projects,
            ),
            'page' => $this->page,
            'pageSize' => $this->pageSize,
            'totalCount' => $this->totalCount,
            'totalPages' => $this->totalPages,
        ];
    }

    public function isPaged(): bool
    {
        return $this->page !== null && $this->pageSize !== null;
    }
}
