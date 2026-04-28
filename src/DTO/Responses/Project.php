<?php

declare(strict_types=1);

namespace Madtec\OmniLeads\DTO\Responses;

use DateTimeImmutable;
use Madtec\OmniLeads\Support\DateParser;

final readonly class Project
{
    /**
     * @param  list<int>|null  $workingDaysOfWeek
     * @param  list<string>|null  $workingDates
     * @param  list<string>|null  $excludedDates
     * @param  list<int>|null  $geoRegions
     * @param  list<ProjectSource>  $projectSources
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
        public string $userId,
        public ?string $userEmail,
        public ?string $userFirstName,
        public ?string $userLastName,
        public ?array $workingDaysOfWeek,
        public ?array $workingDates,
        public ?array $excludedDates,
        public ?array $geoRegions,
        public array $projectSources,
    ) {}

    /**
     * @param  array<int|string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $sources = [];
        if (isset($data['projectSources']) && is_array($data['projectSources'])) {
            foreach ($data['projectSources'] as $row) {
                if (is_array($row)) {
                    $sources[] = ProjectSource::fromArray($row);
                }
            }
        }

        $createdAt = DateParser::parse($data['createdAt'] ?? null) ?? new DateTimeImmutable;

        return new self(
            id: (string) ($data['id'] ?? ''),
            name: (string) ($data['name'] ?? ''),
            description: self::nullableString($data['description'] ?? null),
            limit: (int) ($data['limit'] ?? 0),
            limitTypeId: (int) ($data['limitTypeId'] ?? 0),
            limitTypeName: (string) ($data['limitTypeName'] ?? ''),
            createdAt: $createdAt,
            updatedAt: DateParser::parse($data['updatedAt'] ?? null),
            isActive: (bool) ($data['isActive'] ?? false),
            userId: (string) ($data['userId'] ?? ''),
            userEmail: self::nullableString($data['userEmail'] ?? null),
            userFirstName: self::nullableString($data['userFirstName'] ?? null),
            userLastName: self::nullableString($data['userLastName'] ?? null),
            workingDaysOfWeek: self::intList($data['workingDaysOfWeek'] ?? null),
            workingDates: self::stringList($data['workingDates'] ?? null),
            excludedDates: self::stringList($data['excludedDates'] ?? null),
            geoRegions: self::intList($data['geoRegions'] ?? null),
            projectSources: $sources,
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
            'userId' => $this->userId,
            'userEmail' => $this->userEmail,
            'userFirstName' => $this->userFirstName,
            'userLastName' => $this->userLastName,
            'workingDaysOfWeek' => $this->workingDaysOfWeek,
            'workingDates' => $this->workingDates,
            'excludedDates' => $this->excludedDates,
            'geoRegions' => $this->geoRegions,
            'projectSources' => array_map(
                static fn (ProjectSource $source): array => $source->toArray(),
                $this->projectSources,
            ),
        ];
    }

    private static function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return is_string($value) ? $value : null;
    }

    /**
     * @return list<int>|null
     */
    private static function intList(mixed $value): ?array
    {
        if ($value === null || ! is_array($value)) {
            return null;
        }

        $result = [];
        foreach ($value as $item) {
            if (is_int($item) || is_string($item) && ctype_digit($item)) {
                $result[] = (int) $item;
            }
        }

        return $result;
    }

    /**
     * @return list<string>|null
     */
    private static function stringList(mixed $value): ?array
    {
        if ($value === null || ! is_array($value)) {
            return null;
        }

        $result = [];
        foreach ($value as $item) {
            if (is_scalar($item)) {
                $result[] = (string) $item;
            }
        }

        return $result;
    }
}
