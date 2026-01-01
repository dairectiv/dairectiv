<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Application\Rule\ListRules;

use Dairectiv\Authoring\Domain\Object\Rule\Rule;

final readonly class Output
{
    /**
     * @param list<Rule> $items
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
