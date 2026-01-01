<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Application\Workflow\Step\RemoveStep;

use Dairectiv\SharedKernel\Application\Command\Command;

final readonly class Input implements Command
{
    public function __construct(
        public string $workflowId,
        public string $stepId,
    ) {
    }
}
