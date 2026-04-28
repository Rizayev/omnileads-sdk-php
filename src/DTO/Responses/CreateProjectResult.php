<?php

declare(strict_types=1);

namespace Madtec\OmniLeads\DTO\Responses;

final readonly class CreateProjectResult
{
    public function __construct(
        public string $id,
        public string $message,
    ) {}

    /**
     * @param  array<int|string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (string) ($data['id'] ?? ''),
            message: (string) ($data['message'] ?? ''),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'message' => $this->message,
        ];
    }
}
