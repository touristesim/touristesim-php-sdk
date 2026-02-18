<?php

namespace TouristeSIM\Sdk\Support;

/**
 * Paginated Collection class
 */
class PaginatedCollection extends Collection
{
    private array $pagination = [];

    public function __construct(Collection $items, array $pagination = [])
    {
        parent::__construct($items->all());
        $this->pagination = $pagination;
    }

    /**
     * Get current page
     */
    public function getCurrentPage(): int
    {
        return $this->pagination['current_page'] ?? 1;
    }

    /**
     * Get items per page
     */
    public function getPerPage(): int
    {
        return $this->pagination['per_page'] ?? 50;
    }

    /**
     * Get total items
     */
    public function getTotal(): int
    {
        return $this->pagination['total'] ?? 0;
    }

    /**
     * Get last page
     */
    public function getLastPage(): int
    {
        return $this->pagination['last_page'] ?? 1;
    }

    /**
     * Check if there are more pages
     */
    public function hasMore(): bool
    {
        return $this->pagination['has_more'] ?? false;
    }

    /**
     * Get pagination info
     */
    public function getPagination(): array
    {
        return $this->pagination;
    }

    /**
     * Check if is first page
     */
    public function isFirstPage(): bool
    {
        return $this->getCurrentPage() === 1;
    }

    /**
     * Check if is last page
     */
    public function isLastPage(): bool
    {
        return $this->getCurrentPage() === $this->getLastPage();
    }
}
