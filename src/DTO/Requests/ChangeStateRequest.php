<?php

declare(strict_types=1);

namespace Madtec\OmniLeads\DTO\Requests;

use Madtec\OmniLeads\Enums\ProjectState;

final readonly class ChangeStateRequest
{
    public function __construct(
        public ProjectState $state,
    ) {}

    public static function active(): self
    {
        return new self(ProjectState::ACTIVE);
    }

    public static function paused(): self
    {
        return new self(ProjectState::PAUSED);
    }

    public static function fromString(string $state): self
    {
        return new self(ProjectState::fromInput($state));
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'state' => $this->state->value,
        ];
    }
}
