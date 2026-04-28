<?php

declare(strict_types=1);

namespace Madtec\OmniLeads\DTO\Requests;

use Madtec\OmniLeads\Enums\SourceData;
use Madtec\OmniLeads\Enums\SourceType;
use Madtec\OmniLeads\Exceptions\ConfigurationException;

final readonly class CreateSourceRequest
{
    /**
     * @param  list<string>|null  $sourceValues
     */
    public function __construct(
        public SourceType|int $sourceTypeId,
        public SourceData|int $sourceDataId,
        public ?string $sourceValue = null,
        public ?array $sourceValues = null,
        public ?int $limit = null,
        public ?int $projectSourceStatusId = 1,
    ) {
        if ($sourceValue === null && ($sourceValues === null || $sourceValues === [])) {
            throw new ConfigurationException(
                'CreateSourceRequest: provide at least one of sourceValue or sourceValues',
            );
        }

        if ($limit !== null && $limit < 0) {
            throw new ConfigurationException('CreateSourceRequest: limit must be >= 0');
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $payload = [
            'sourceTypeId' => $this->sourceTypeId instanceof SourceType
                ? $this->sourceTypeId->value
                : $this->sourceTypeId,
            'sourceDataId' => $this->sourceDataId instanceof SourceData
                ? $this->sourceDataId->value
                : $this->sourceDataId,
        ];

        if ($this->sourceValue !== null) {
            $payload['sourceValue'] = $this->sourceValue;
        }

        if ($this->sourceValues !== null) {
            $payload['sourceValues'] = $this->sourceValues;
        }

        if ($this->limit !== null) {
            $payload['limit'] = $this->limit;
        }

        if ($this->projectSourceStatusId !== null) {
            $payload['projectSourceStatusId'] = $this->projectSourceStatusId;
        }

        return $payload;
    }
}
