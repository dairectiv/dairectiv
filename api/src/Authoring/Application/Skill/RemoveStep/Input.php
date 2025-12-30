<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Application\Skill\RemoveStep;

use Dairectiv\SharedKernel\Application\Command\Command;

final readonly class Input implements Command
{
    public function __construct(
        public string $skillId,
        public string $stepId,
    ) {
    }
}
