<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Domain\Object\Rule;

use Dairectiv\Authoring\Domain\Object\Directive\DirectiveState;

final readonly class RuleSearchCriteria
{
    public function __construct(
        public ?string $search = null,
        public ?DirectiveState $state = null,
        public string $sortBy = 'createdAt',
        public string $sortOrder = 'desc',
    ) {
    }
}
