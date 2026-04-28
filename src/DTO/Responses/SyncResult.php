<?php

declare(strict_types=1);

namespace Madtec\OmniLeads\DTO\Responses;

final readonly class SyncResult
{
    /**
     * @param  array{created: int, updated: int, disabled: int}  $projects
     * @param  array{created: int, updated: int, disabled: int}  $segments
     * @param  list<string>  $errors
     */
    public function __construct(
        public bool $success,
        public array $projects,
        public array $segments,
        public array $errors,
    ) {}

    /**
     * @param  array<int|string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $summary = [];
        if (isset($data['summary']) && is_array($data['summary'])) {
            $summary = $data['summary'];
        }

        $projects = self::extractCounters($summary['projects'] ?? null);
        $segments = self::extractCounters($summary['segments'] ?? null);

        $errors = [];
        if (isset($data['errors']) && is_array($data['errors'])) {
            foreach ($data['errors'] as $error) {
                if (is_string($error)) {
                    $errors[] = $error;
                }
            }
        }

        return new self(
            success: (bool) ($data['success'] ?? false),
            projects: $projects,
            segments: $segments,
            errors: $errors,
        );
    }

    /**
     * @return array{created: int, updated: int, disabled: int}
     */
    private static function extractCounters(mixed $value): array
    {
        if (! is_array($value)) {
            return ['created' => 0, 'updated' => 0, 'disabled' => 0];
        }

        return [
            'created' => (int) ($value['created'] ?? 0),
            'updated' => (int) ($value['updated'] ?? 0),
            'disabled' => (int) ($value['disabled'] ?? 0),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'summary' => [
                'projects' => $this->projects,
                'segments' => $this->segments,
            ],
            'errors' => $this->errors,
        ];
    }
}
