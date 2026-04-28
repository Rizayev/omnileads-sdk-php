<?php

declare(strict_types=1);

namespace Madtec\OmniLeads\DTO\Requests;

use Madtec\OmniLeads\Enums\DayOfWeek;
use Madtec\OmniLeads\Exceptions\ConfigurationException;

final readonly class SyncProjectDTO
{
    /**
     * @param  list<SyncSegmentDTO>  $segments
     * @param  list<DayOfWeek|int>|null  $workingDaysOfWeek
     * @param  list<string>|null  $workingDates
     * @param  list<string>|null  $excludedDates
     * @param  list<int>|null  $geoRegions
     */
    public function __construct(
        public string $name,
        public int $limit,
        public string $limitType,
        public array $segments,
        public ?string $description = null,
        public ?array $workingDaysOfWeek = null,
        public ?array $workingDates = null,
        public ?array $excludedDates = null,
        public ?array $geoRegions = null,
    ) {
        if (trim($name) === '') {
            throw new ConfigurationException('SyncProjectDTO: name must not be empty');
        }

        if ($limit < 0) {
            throw new ConfigurationException('SyncProjectDTO: limit must be >= 0');
        }

        if (trim($limitType) === '') {
            throw new ConfigurationException('SyncProjectDTO: limitType must not be empty');
        }

        foreach ($segments as $segment) {
            if (! $segment instanceof SyncSegmentDTO) {
                throw new ConfigurationException('SyncProjectDTO: segments must be a list of SyncSegmentDTO');
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $payload = [
            'name' => $this->name,
            'limit' => $this->limit,
            'limitType' => $this->limitType,
            'segments' => array_map(
                static fn (SyncSegmentDTO $segment): array => $segment->toArray(),
                $this->segments,
            ),
        ];

        if ($this->description !== null) {
            $payload['description'] = $this->description;
        }

        if ($this->workingDaysOfWeek !== null) {
            $payload['workingDaysOfWeek'] = array_map(
                static fn (DayOfWeek|int $day): int => $day instanceof DayOfWeek ? $day->value : $day,
                $this->workingDaysOfWeek,
            );
        }

        if ($this->workingDates !== null) {
            $payload['workingDates'] = $this->workingDates;
        }

        if ($this->excludedDates !== null) {
            $payload['excludedDates'] = $this->excludedDates;
        }

        if ($this->geoRegions !== null) {
            $payload['geoRegions'] = $this->geoRegions;
        }

        return $payload;
    }
}
