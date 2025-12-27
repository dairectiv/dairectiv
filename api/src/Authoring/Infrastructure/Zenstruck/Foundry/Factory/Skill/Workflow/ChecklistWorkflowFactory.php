<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Infrastructure\Zenstruck\Foundry\Factory\Skill\Workflow;

use Dairectiv\Authoring\Domain\Object\Skill\Workflow\ChecklistWorkflow;
use Dairectiv\Authoring\Domain\Object\Skill\Workflow\SkillStep;
use Zenstruck\Foundry\Object\Instantiator;

/**
 * @extends SkillWorkflowFactory<ChecklistWorkflow>
 */
final class ChecklistWorkflowFactory extends SkillWorkflowFactory
{
    protected function defaults(): array
    {
        return [
            'items' => SkillStepFactory::new()->many(3),
        ];
    }

    public static function class(): string
    {
        return ChecklistWorkflow::class;
    }

    protected function initialize(): static
    {
        return $this->instantiateWith(Instantiator::use(ChecklistWorkflow::create(...)));
    }

    /**
     * @param iterable<SkillStep> $items
     */
    public function withItems(iterable $items): self
    {
        return $this->with(['items' => $items]);
    }
}
