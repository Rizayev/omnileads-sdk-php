<?php

declare(strict_types=1);

namespace Madtec\OmniLeads\DTO\Responses;

/**
 * Generic paged result wrapper.
 *
 * @template T
 */
final readonly class PagedResult
{
    /**
     * @param  list<T>  $items
     */
    public function __construct(
        public array $items,
        public int $page,
        public int $pageSize,
        public int $totalCount,
        public int $totalPages,
    ) {}

    /**
     * @template U
     *
     * @param  array<int|string, mixed>  $data
     * @param  callable(array<string, mixed>): U  $itemFactory
     * @return self<U>
     */
    public static function fromArray(array $data, callable $itemFactory, string $itemsKey = 'items'): self
    {
        $items = [];
        if (isset($data[$itemsKey]) && is_array($data[$itemsKey])) {
            foreach ($data[$itemsKey] as $row) {
                if (is_array($row)) {
                    $items[] = $itemFactory($row);
                }
            }
        }

        return new self(
            items: $items,
            page: (int) ($data['page'] ?? 1),
            pageSize: (int) ($data['pageSize'] ?? 0),
            totalCount: (int) ($data['totalCount'] ?? count($items)),
            totalPages: (int) ($data['totalPages'] ?? 1),
        );
    }

    public function hasNextPage(): bool
    {
        return $this->page < $this->totalPages;
    }
}
