<?php

declare(strict_types=1);

namespace Madtec\OmniLeads\DTO\Responses;

use DateTimeImmutable;
use Madtec\OmniLeads\Support\DateParser;

final readonly class ProjectSource
{
    /**
     * @param  list<string>  $sourceValues
     */
    public function __construct(
        public string $id,
        public int $sourceTypeId,
        public string $sourceTypeName,
        public int $sourceDataId,
        public string $sourceDataName,
        public int $projectSourceStatusId,
        public string $projectSourceStatusName,
        public ?string $sourceValue,
        public array $sourceValues,
        public int $limit,
        public DateTimeImmutable $createdAt,
        public ?DateTimeImmutable $updatedAt,
    ) {}

    /**
     * @param  array<int|string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $sourceValues = [];
        if (isset($data['sourceValues']) && is_array($data['sourceValues'])) {
            foreach ($data['sourceValues'] as $value) {
                if (is_scalar($value)) {
                    $sourceValues[] = (string) $value;
                }
            }
        }

        $sourceValue = $data['sourceValue'] ?? null;
        if ($sourceValue !== null && ! is_string($sourceValue)) {
            $sourceValue = is_scalar($sourceValue) ? (string) $sourceValue : null;
        }

        $createdAt = DateParser::parse($data['createdAt'] ?? null) ?? new DateTimeImmutable;

        return new self(
            id: (string) ($data['id'] ?? ''),
            sourceTypeId: (int) ($data['sourceTypeId'] ?? 0),
            sourceTypeName: (string) ($data['sourceTypeName'] ?? ''),
            sourceDataId: (int) ($data['sourceDataId'] ?? 0),
            sourceDataName: (string) ($data['sourceDataName'] ?? ''),
            projectSourceStatusId: (int) ($data['projectSourceStatusId'] ?? 0),
            projectSourceStatusName: (string) ($data['projectSourceStatusName'] ?? ''),
            sourceValue: $sourceValue,
            sourceValues: $sourceValues,
            limit: (int) ($data['limit'] ?? 0),
            createdAt: $createdAt,
            updatedAt: DateParser::parse($data['updatedAt'] ?? null),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'sourceTypeId' => $this->sourceTypeId,
            'sourceTypeName' => $this->sourceTypeName,
            'sourceDataId' => $this->sourceDataId,
            'sourceDataName' => $this->sourceDataName,
            'projectSourceStatusId' => $this->projectSourceStatusId,
            'projectSourceStatusName' => $this->projectSourceStatusName,
            'sourceValue' => $this->sourceValue,
            'sourceValues' => $this->sourceValues,
            'limit' => $this->limit,
            'createdAt' => DateParser::format($this->createdAt),
            'updatedAt' => $this->updatedAt !== null ? DateParser::format($this->updatedAt) : null,
        ];
    }
}
