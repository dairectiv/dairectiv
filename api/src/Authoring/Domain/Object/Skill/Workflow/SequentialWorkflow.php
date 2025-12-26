<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Domain\Object\Skill\Workflow;

use Dairectiv\SharedKernel\Domain\Object\Assert;

/**
 * Sequential workflow for step-by-step processes (like git-commit skill).
 */
final readonly class SequentialWorkflow extends SkillWorkflow
{
    /**
     * @param list<SkillStep> $steps
     */
    private function __construct(public array $steps)
    {
    }

    /**
     * @param list<SkillStep> $steps
     */
    public static function create(array $steps): self
    {
        Assert::notEmpty($steps, 'Sequential workflow must have at least one step.');

        return new self($steps);
    }

    public function getType(): WorkflowType
    {
        return WorkflowType::Sequential;
    }

    public function stepCount(): int
    {
        return \count($this->steps);
    }

    public static function fromState(array $state): static
    {
        Assert::keyExists($state, 'steps');
        Assert::isArray($state['steps']);
        Assert::allIsArray($state['steps']);

        $steps = array_values(array_map(SkillStep::fromArray(...), $state['steps']));

        return new self($steps);
    }

    public function toArray(): array
    {
        return [
            'type'  => $this->getType()->value,
            'steps' => array_map(static fn (SkillStep $step): array => $step->toArray(), $this->steps),
        ];
    }
}
