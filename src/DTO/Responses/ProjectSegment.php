<?php

declare(strict_types=1);

namespace Madtec\OmniLeads\DTO\Responses;

final readonly class ProjectSegment
{
    /**
     * @param  list<string>  $sourceValues
     * @param  list<DayPhone>  $phones
     */
    public function __construct(
        public int $projectSourceId,
        public int $sourceTypeId,
        public string $sourceTypeName,
        public int $sourceDataId,
        public string $sourceDataName,
        public array $sourceValues,
        public array $phones,
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

        $phones = [];
        if (isset($data['phones']) && is_array($data['phones'])) {
            foreach ($data['phones'] as $row) {
                if (is_array($row)) {
                    $phones[] = DayPhone::fromArray($row);
                }
            }
        }

        return new self(
            projectSourceId: (int) ($data['projectSourceId'] ?? 0),
            sourceTypeId: (int) ($data['sourceTypeId'] ?? 0),
            sourceTypeName: (string) ($data['sourceTypeName'] ?? ''),
            sourceDataId: (int) ($data['sourceDataId'] ?? 0),
            sourceDataName: (string) ($data['sourceDataName'] ?? ''),
            sourceValues: $sourceValues,
            phones: $phones,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'projectSourceId' => $this->projectSourceId,
            'sourceTypeId' => $this->sourceTypeId,
            'sourceTypeName' => $this->sourceTypeName,
            'sourceDataId' => $this->sourceDataId,
            'sourceDataName' => $this->sourceDataName,
            'sourceValues' => $this->sourceValues,
            'phones' => array_map(
                static fn (DayPhone $phone): array => $phone->toArray(),
                $this->phones,
            ),
        ];
    }
}
