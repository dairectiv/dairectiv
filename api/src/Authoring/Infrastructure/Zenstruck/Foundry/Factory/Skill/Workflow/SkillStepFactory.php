<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Infrastructure\Zenstruck\Foundry\Factory\Skill\Workflow;

use Dairectiv\Authoring\Domain\Object\Skill\Workflow\SkillStep;
use Dairectiv\Authoring\Domain\Object\Skill\Workflow\StepType;
use Zenstruck\Foundry\Object\Instantiator;
use Zenstruck\Foundry\ObjectFactory;

/**
 * @extends ObjectFactory<SkillStep>
 */
final class SkillStepFactory extends ObjectFactory
{
    protected function defaults(): array
    {
        return [
            'order'     => 1,
            'title'     => self::faker()->sentence(3),
            'content'   => self::faker()->paragraph(2),
            'type'      => self::faker()->randomElement(StepType::cases()),
            'condition' => self::faker()->optional()->paragraph(),
        ];
    }

    public static function class(): string
    {
        return SkillStep::class;
    }

    protected function initialize(): static
    {
        return $this->instantiateWith(Instantiator::use(SkillStep::create(...)));
    }

    public static function withOrder(int $order): self
    {
        return self::new()->with(['order' => $order]);
    }

    public function withoutCondition(): self
    {
        return $this->with(['condition' => null]);
    }

    public function withCondition(): self
    {
        return $this->with(['condition' => self::faker()->paragraph()]);
    }
}
