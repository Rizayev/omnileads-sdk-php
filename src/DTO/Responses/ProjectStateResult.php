<?php

declare(strict_types=1);

namespace Madtec\OmniLeads\DTO\Responses;

use Madtec\OmniLeads\Enums\ProjectState;

final readonly class ProjectStateResult
{
    public function __construct(
        public string $projectId,
        public ProjectState $state,
        public bool $isActive,
        public bool $changed,
        public string $message,
    ) {}

    /**
     * @param  array<int|string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $stateRaw = $data['state'] ?? 'active';
        $stateString = is_scalar($stateRaw) ? (string) $stateRaw : 'active';

        return new self(
            projectId: (string) ($data['projectId'] ?? ''),
            state: ProjectState::fromInput($stateString),
            isActive: (bool) ($data['isActive'] ?? false),
            changed: (bool) ($data['changed'] ?? false),
            message: (string) ($data['message'] ?? ''),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'projectId' => $this->projectId,
            'state' => $this->state->value,
            'isActive' => $this->isActive,
            'changed' => $this->changed,
            'message' => $this->message,
        ];
    }
}
