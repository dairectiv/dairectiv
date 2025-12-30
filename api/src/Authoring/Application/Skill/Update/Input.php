<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Application\Skill\Update;

use Dairectiv\SharedKernel\Application\Command\Command;

final readonly class Input implements Command
{
    public function __construct(
        public string $id,
        public ?string $name = null,
        public ?string $description = null,
        public ?string $content = null,
    ) {
    }
}
