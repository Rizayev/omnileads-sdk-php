<?php

declare(strict_types=1);

namespace Madtec\OmniLeads\DTO\Requests;

use Madtec\OmniLeads\Enums\DayOfWeek;
use Madtec\OmniLeads\Enums\ProjectState;
use Madtec\OmniLeads\Exceptions\ConfigurationException;

final readonly class UpdateProjectRequest
{
    /**
     * @param  list<DayOfWeek|int>|null  $workingDaysOfWeek
     * @param  list<string>|null  $workingDates
     * @param  list<string>|null  $excludedDates
     * @param  list<int>|null  $geoRegions
     */
    public function __construct(
        public ?string $name = null,
        public ?string $description = null,
        public ?ProjectState $state = null,
        public ?int $limit = null,
        public ?int $limitTypeId = null,
        public ?array $workingDaysOfWeek = null,
        public ?array $workingDates = null,
        public ?array $excludedDates = null,
        public ?array $geoRegions = null,
    ) {
        if ($name !== null && trim($name) === '') {
            throw new ConfigurationException('UpdateProjectRequest: name must not be empty when provided');
        }

        if ($limit !== null && $limit < 0) {
            throw new ConfigurationException('UpdateProjectRequest: limit must be >= 0');
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $payload = [];

        if ($this->name !== null) {
            $payload['name'] = $this->name;
        }

        if ($this->description !== null) {
            $payload['description'] = $this->description;
        }

        if ($this->state !== null) {
            $payload['state'] = $this->state->value;
        }

        if ($this->limit !== null) {
            $payload['limit'] = $this->limit;
        }

        if ($this->limitTypeId !== null) {
            $payload['limitTypeId'] = $this->limitTypeId;
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
