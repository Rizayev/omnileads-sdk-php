<?php

declare(strict_types=1);

namespace Madtec\OmniLeads\DTO\Requests;

use Madtec\OmniLeads\Enums\SourceData;
use Madtec\OmniLeads\Enums\SourceType;
use Madtec\OmniLeads\Exceptions\ConfigurationException;

final readonly class SyncSegmentDTO
{
    /**
     * @param  list<string>|null  $sourceValues
     */
    public function __construct(
        public string $sourceType,
        public string $sourceData,
        public ?array $sourceValues = null,
        public ?int $limit = null,
    ) {
        if (trim($sourceType) === '') {
            throw new ConfigurationException('SyncSegmentDTO: sourceType must not be empty');
        }

        if (trim($sourceData) === '') {
            throw new ConfigurationException('SyncSegmentDTO: sourceData must not be empty');
        }

        if ($limit !== null && $limit < 0) {
            throw new ConfigurationException('SyncSegmentDTO: limit must be >= 0');
        }
    }

    public static function fromEnums(
        SourceType $sourceType,
        SourceData $sourceData,
        ?array $sourceValues = null,
        ?int $limit = null,
    ): self {
        return new self(
            sourceType: $sourceType->label(),
            sourceData: $sourceData->label(),
            sourceValues: $sourceValues,
            limit: $limit,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $payload = [
            'sourceType' => $this->sourceType,
            'sourceData' => $this->sourceData,
        ];

        if ($this->sourceValues !== null) {
            $payload['sourceValues'] = $this->sourceValues;
        }

        if ($this->limit !== null) {
            $payload['limit'] = $this->limit;
        }

        return $payload;
    }
}
