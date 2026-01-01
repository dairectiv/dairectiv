<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Domain\Object\Workflow;

use Dairectiv\Authoring\Domain\Object\Directive\DirectiveState;

final readonly class WorkflowSearchCriteria
{
    public function __construct(
        public ?string $search = null,
        public ?DirectiveState $state = null,
        public string $sortBy = 'createdAt',
        public string $sortOrder = 'desc',
    ) {
    }
}
