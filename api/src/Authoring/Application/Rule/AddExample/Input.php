<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Application\Rule\AddExample;

use Dairectiv\SharedKernel\Application\Command\Command;

final readonly class Input implements Command
{
    public function __construct(
        public string $ruleId,
        public ?string $good = null,
        public ?string $bad = null,
        public ?string $explanation = null,
    ) {
    }
}
