<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Application\Rule\Draft;

use Dairectiv\SharedKernel\Application\Command\Command;

final readonly class Input implements Command
{
    /**
     * @param list<array{good?: ?string, bad?: ?string, explanation?: ?string}> $examples
     */
    public function __construct(
        public string $id,
        public string $name,
        public string $description,
        public string $content,
        public array $examples = [],
    ) {
    }
}
