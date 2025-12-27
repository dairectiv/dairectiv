<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Infrastructure\Zenstruck\Foundry\Factory\Skill\Workflow;

use Dairectiv\Authoring\Domain\Object\Skill\Workflow\HybridWorkflow;
use Dairectiv\Authoring\Domain\Object\Skill\Workflow\SkillStep;
use Dairectiv\Authoring\Domain\Object\Skill\Workflow\SkillTemplate;
use Zenstruck\Foundry\Object\Instantiator;

/**
 * @extends SkillWorkflowFactory<HybridWorkflow>
 */
final class HybridWorkflowFactory extends SkillWorkflowFactory
{
    protected function defaults(): array
    {
        return [
            'templates' => SkillTemplateFactory::new()->many(3),
            'steps'     => SkillStepFactory::createSequence([
                ['order' => 1],
                ['order' => 2],
                ['order' => 3],
            ]),
        ];
    }

    public static function class(): string
    {
        return HybridWorkflow::class;
    }

    protected function initialize(): static
    {
        return $this->instantiateWith(Instantiator::use(HybridWorkflow::create(...)));
    }

    /**
     * @param iterable<SkillStep> $steps
     */
    public function withSteps(iterable $steps): self
    {
        return $this->with(['steps' => $steps]);
    }

    /**
     * @param iterable<SkillTemplate> $templates
     */
    public function withTemplates(iterable $templates): self
    {
        return $this->with(['templates' => $templates]);
    }
}
