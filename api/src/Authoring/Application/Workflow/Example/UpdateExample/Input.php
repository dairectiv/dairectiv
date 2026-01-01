<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Application\Workflow\Example\UpdateExample;

use Dairectiv\SharedKernel\Application\Command\Command;

final readonly class Input implements Command
{
    public function __construct(
        public string $workflowId,
        public string $exampleId,
        public string $scenario,
        public string $input,
        public string $output,
        public ?string $explanation = null,
    ) {
    }
}
