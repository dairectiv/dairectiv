<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Infrastructure\Zenstruck\Foundry\Factory\Skill\Workflow;

use Dairectiv\Authoring\Domain\Object\Skill\Workflow\SkillWorkflow;
use Zenstruck\Foundry\ObjectFactory;

/**
 * @template T of SkillWorkflow
 * @extends ObjectFactory<T>
 */
abstract class SkillWorkflowFactory extends ObjectFactory
{
    public static function random(): static
    {
        /**
         * @var callable(): static<T>
         */
        $callback = self::faker()->randomElement([
            static fn () => ChecklistWorkflowFactory::new(),
            static fn () => TemplateWorkflowFactory::new(),
            static fn () => HybridWorkflowFactory::new(),
            static fn () => SequentialWorkflowFactory::new(),
        ]);

        return $callback();
    }
}
