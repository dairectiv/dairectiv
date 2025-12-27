<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Infrastructure\Zenstruck\Foundry\Factory\Skill\Workflow;

use Dairectiv\Authoring\Domain\Object\Skill\Workflow\SequentialWorkflow;
use Dairectiv\Authoring\Domain\Object\Skill\Workflow\SkillStep;
use Zenstruck\Foundry\Object\Instantiator;

/**
 * @extends SkillWorkflowFactory<SequentialWorkflow>
 */
final class SequentialWorkflowFactory extends SkillWorkflowFactory
{
    protected function defaults(): array
    {
        return [
            'steps' => SkillStepFactory::createSequence([
                ['order' => 1],
                ['order' => 2],
                ['order' => 3],
            ]),
        ];
    }

    public static function class(): string
    {
        return SequentialWorkflow::class;
    }

    protected function initialize(): static
    {
        return $this->instantiateWith(Instantiator::use(SequentialWorkflow::create(...)));
    }

    /**
     * @param iterable<SkillStep> $steps
     */
    public function withSteps(iterable $steps): self
    {
        return $this->with(['steps' => $steps]);
    }
}
