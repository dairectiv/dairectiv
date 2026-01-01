<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Application\Workflow\ListWorkflows;

use Dairectiv\Authoring\Domain\Object\Workflow\Workflow;

final readonly class Output
{
    /**
     * @param list<Workflow> $items
     */
    public function __construct(
        public array $items,
        public int $total,
        public int $page,
        public int $limit,
    ) {
    }

    public function totalPages(): int
    {
        return (int) ceil($this->total / $this->limit);
    }

    public function hasNextPage(): bool
    {
        return $this->page < $this->totalPages();
    }

    public function hasPreviousPage(): bool
    {
        return $this->page > 1;
    }
}
