<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Application\Workflow\MoveStep;

use Dairectiv\SharedKernel\Application\Command\Command;

final readonly class Input implements Command
{
    public function __construct(
        public string $workflowId,
        public string $stepId,
        public ?string $afterStepId = null,
    ) {
    }
}
