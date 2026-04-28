<?php

declare(strict_types=1);

namespace Madtec\OmniLeads\DTO\Requests;

use Madtec\OmniLeads\Enums\DayOfWeek;
use Madtec\OmniLeads\Enums\ProjectState;
use Madtec\OmniLeads\Exceptions\ConfigurationException;

final readonly class CreateProjectRequest
{
    /**
     * @param  list<DayOfWeek|int>|null  $workingDaysOfWeek
     * @param  list<string>|null  $workingDates
     * @param  list<string>|null  $excludedDates
     * @param  list<int>|null  $geoRegions
     */
    public function __construct(
        public string $name,
        public int $limit,
        public int $limitTypeId,
        public ?string $description = null,
        public ?ProjectState $state = null,
        public ?array $workingDaysOfWeek = null,
        public ?array $workingDates = null,
        public ?array $excludedDates = null,
        public ?array $geoRegions = null,
    ) {
        if (trim($name) === '') {
            throw new ConfigurationException('CreateProjectRequest: name must not be empty');
        }

        if ($limit < 0) {
            throw new ConfigurationException('CreateProjectRequest: limit must be >= 0');
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
            'limitTypeId' => $this->limitTypeId,
        ];

        if ($this->description !== null) {
            $payload['description'] = $this->description;
        }

        if ($this->state !== null) {
            $payload['state'] = $this->state->value;
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
