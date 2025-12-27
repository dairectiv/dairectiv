<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Infrastructure\Zenstruck\Foundry\Factory\Skill\Workflow;

use Dairectiv\Authoring\Domain\Object\Skill\Workflow\SkillTemplate;
use Dairectiv\Authoring\Domain\Object\Skill\Workflow\TemplateWorkflow;
use Zenstruck\Foundry\Object\Instantiator;

/**
 * @extends SkillWorkflowFactory<TemplateWorkflow>
 */
final class TemplateWorkflowFactory extends SkillWorkflowFactory
{
    protected function defaults(): array
    {
        return [
            'templates' => SkillTemplateFactory::new()->many(3),
        ];
    }

    public static function class(): string
    {
        return TemplateWorkflow::class;
    }

    protected function initialize(): static
    {
        return $this->instantiateWith(Instantiator::use(TemplateWorkflow::create(...)));
    }

    /**
     * @param iterable<SkillTemplate> $templates
     */
    public function withTemplates(iterable $templates): self
    {
        return $this->with(['templates' => $templates]);
    }
}
