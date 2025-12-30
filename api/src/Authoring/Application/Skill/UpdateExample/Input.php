<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Application\Skill\UpdateExample;

use Dairectiv\SharedKernel\Application\Command\Command;

final readonly class Input implements Command
{
    public function __construct(
        public string $skillId,
        public string $exampleId,
        public ?string $scenario = null,
        public ?string $input = null,
        public ?string $output = null,
        public ?string $explanation = null,
    ) {
    }
}
