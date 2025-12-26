<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Domain\Object\Skill\Workflow;

use Dairectiv\SharedKernel\Domain\Object\Assert;
use Dairectiv\SharedKernel\Domain\Object\ValueObject\ObjectValue;

abstract readonly class SkillWorkflow implements ObjectValue
{
    abstract public function getType(): WorkflowType;

    /**
     * @param array<array-key, mixed> $state
     */
    abstract protected static function fromState(array $state): static;

    public static function fromArray(array $state): static
    {
        Assert::keyExists($state, 'type');
        Assert::string($state['type']);

        $type = WorkflowType::from($state['type']);

        /** @phpstan-ignore return.type */
        return match ($type) {
            WorkflowType::Sequential => SequentialWorkflow::fromState($state),
            WorkflowType::Template   => TemplateWorkflow::fromState($state),
            WorkflowType::Checklist  => ChecklistWorkflow::fromState($state),
            WorkflowType::Hybrid     => HybridWorkflow::fromState($state),
        };
    }
}
