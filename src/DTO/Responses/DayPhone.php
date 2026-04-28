<?php

declare(strict_types=1);

namespace Madtec\OmniLeads\DTO\Responses;

use DateTimeImmutable;
use Madtec\OmniLeads\Support\DateParser;

final readonly class DayPhone
{
    public function __construct(
        public string $phone,
        public DateTimeImmutable $createdAt,
    ) {}

    /**
     * @param  array<int|string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $phoneRaw = $data['phone'] ?? '';
        $phone = is_scalar($phoneRaw) ? (string) $phoneRaw : '';

        $createdAt = DateParser::parse($data['createdAt'] ?? null) ?? new DateTimeImmutable;

        return new self(
            phone: $phone,
            createdAt: $createdAt,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'phone' => $this->phone,
            'createdAt' => DateParser::format($this->createdAt),
        ];
    }
}
