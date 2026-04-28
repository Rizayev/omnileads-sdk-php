<?php

declare(strict_types=1);

namespace Madtec\OmniLeads\DTO\Responses;

use DateTimeImmutable;
use Madtec\OmniLeads\Support\DateParser;

final readonly class ProjectListItem
{
    /**
     * @param  list<string>  $sourceValues
     */
    public function __construct(
        public string $id,
        public string $name,
        public ?string $description,
        public int $limit,
        public int $limitTypeId,
        public string $limitTypeName,
        public DateTimeImmutable $createdAt,
        public ?DateTimeImmutable $updatedAt,
        public bool $isActive,
        public int $sourcesCount,
        public array $sourceValues,
    ) {}

    /**
     * @param  array<int|string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $description = $data['description'] ?? null;
        if ($description !== null && ! is_string($description)) {
            $description = null;
        }

        $sourceValues = [];
        if (isset($data['sourceValues']) && is_array($data['sourceValues'])) {
            foreach ($data['sourceValues'] as $value) {
                if (is_scalar($value)) {
                    $sourceValues[] = (string) $value;
                }
            }
        }

        $createdAt = DateParser::parse($data['createdAt'] ?? null);
        if ($createdAt === null) {
            $createdAt = new DateTimeImmutable;
        }

        return new self(
            id: (string) ($data['id'] ?? ''),
            name: (string) ($data['name'] ?? ''),
            description: $description,
            limit: (int) ($data['limit'] ?? 0),
            limitTypeId: (int) ($data['limitTypeId'] ?? 0),
            limitTypeName: (string) ($data['limitTypeName'] ?? ''),
            createdAt: $createdAt,
            updatedAt: DateParser::parse($data['updatedAt'] ?? null),
            isActive: (bool) ($data['isActive'] ?? false),
            sourcesCount: (int) ($data['sourcesCount'] ?? 0),
            sourceValues: $sourceValues,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'limit' => $this->limit,
            'limitTypeId' => $this->limitTypeId,
            'limitTypeName' => $this->limitTypeName,
            'createdAt' => DateParser::format($this->createdAt),
            'updatedAt' => $this->updatedAt !== null ? DateParser::format($this->updatedAt) : null,
            'isActive' => $this->isActive,
            'sourcesCount' => $this->sourcesCount,
            'sourceValues' => $this->sourceValues,
        ];
    }
}
