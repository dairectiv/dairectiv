<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Application\Workflow\ListWorkflows;

use Dairectiv\SharedKernel\Application\Query\Query;

final readonly class Input implements Query
{
    public function __construct(
        public int $page = 1,
        public int $limit = 20,
        public ?string $search = null,
        public ?string $state = null,
        public string $sortBy = 'createdAt',
        public string $sortOrder = 'desc',
    ) {
    }
}
