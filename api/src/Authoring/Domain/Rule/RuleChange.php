<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Domain\Rule;

use Dairectiv\Authoring\Domain\ChangeSet\Change;

final readonly class RuleChange extends Change
{
    public function __construct(
        public ?RuleDescription $description = null,
        public ?RuleContent $content = null,
        public ?RuleExamples $examples = null,
    ) {
    }
}
