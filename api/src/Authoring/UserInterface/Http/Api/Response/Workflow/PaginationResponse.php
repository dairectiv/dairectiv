<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\UserInterface\Http\Api\Response\Workflow;

final readonly class PaginationResponse
{
    public function __construct(
        public int $page,
        public int $limit,
        public int $total,
        public int $totalPages,
        public bool $hasNextPage,
        public bool $hasPreviousPage,
    ) {
    }
}
