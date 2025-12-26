<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Application\Skill\Draft;

use Dairectiv\SharedKernel\Application\Command\Command;

final readonly class Input implements Command
{
    /**
     * @param array{
     *     type: 'sequential'|'template'|'checklist'|'hybrid',
     *     steps?: list<array{order: int, title: string, content: string, type: string, condition?: ?string}>,
     *     templates?: list<array{name: string, content: string, description?: ?string}>,
     *     items?: list<array{order: int, title: string, content: string, type: string, condition?: ?string}>
     * } $workflow
     * @param list<array{scenario: string, input: string, output: string, explanation?: ?string}> $examples
     */
    public function __construct(
        public string $id,
        public string $name,
        public string $description,
        public string $content,
        public array $workflow,
        public array $examples = [],
    ) {
    }
}
