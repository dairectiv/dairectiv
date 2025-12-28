<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Application\Skill\Update;

use Dairectiv\SharedKernel\Application\Command\Command;

final readonly class Input implements Command
{
    /**
     * @param array{
     *     type: 'sequential'|'template'|'checklist'|'hybrid',
     *     steps?: list<array{order: int, title: string, content: string, type: string, condition?: ?string}>,
     *     templates?: list<array{name: string, content: string, description?: ?string}>,
     *     items?: list<array{order: int, title: string, content: string, type: string, condition?: ?string}>
     * }|null $workflow
     * @param list<array{scenario: string, input: string, output: string, explanation?: ?string}>|null $examples
     */
    public function __construct(
        public string $id,
        public ?string $name = null,
        public ?string $description = null,
        public ?string $content = null,
        public ?array $workflow = null,
        public ?array $examples = null,
    ) {
    }
}
