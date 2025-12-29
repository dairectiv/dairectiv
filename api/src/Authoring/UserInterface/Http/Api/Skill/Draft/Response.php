<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\UserInterface\Http\Api\Skill\Draft;

use Dairectiv\Authoring\Domain\Object\Skill\Skill;

/**
 * @phpstan-type TStep=array{order: int, title: string, content: string, type: string, condition?: ?string}
 * @phpstan-type TTemplate=array{name: string, content: string, description?: ?string}
 * @phpstan-type TChecklistWorkflow=array{
 *      type: 'checklist',
 *      items: list<TStep>
 * }
 * @phpstan-type THybridWorkflow=array{
 *      type: 'hybrid',
 *      steps: list<TStep>,
 *      templates: list<TTemplate>,
 * }
 * @phpstan-type TSequentialWorkflow=array{
 *      type: 'sequential',
 *      steps: list<TStep>,
 * }
 * @phpstan-type TTemplateWorkflow=array{
 *      type: 'template',
 *      templates: list<TTemplate>,
 * }
 * @phpstan-type TExample=array{scenario: string, input: string, output: string, explanation?: ?string}
 */
final readonly class Response
{
    /**
     * @param TChecklistWorkflow|THybridWorkflow|TSequentialWorkflow|TTemplateWorkflow $workflow
     * @param list<TExample> $examples
     */
    public function __construct(
        public string $id,
        public string $state,
        public string $createdAt,
        public string $updatedAt,
        public string $name,
        public string $description,
        public string $content,
        public array $workflow,
        public array $examples,
    ) {
    }

    public static function fromSkill(Skill $skill): self
    {
        /** @var array{examples: list<TExample>} $examples */
        $examples = $skill->examples->toArray();

        /** @var TChecklistWorkflow|THybridWorkflow|TSequentialWorkflow|TTemplateWorkflow $workflow */
        $workflow = $skill->workflow->toArray();

        return new self(
            (string) $skill->id,
            $skill->state->value,
            $skill->createdAt->toIso8601String(),
            $skill->updatedAt->toIso8601String(),
            (string) $skill->metadata->name,
            (string) $skill->metadata->description,
            (string) $skill->content,
            $workflow,
            $examples['examples'],
        );
    }
}
