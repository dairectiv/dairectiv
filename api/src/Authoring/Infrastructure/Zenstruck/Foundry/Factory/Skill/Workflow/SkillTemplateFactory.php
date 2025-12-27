<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Infrastructure\Zenstruck\Foundry\Factory\Skill\Workflow;

use Dairectiv\Authoring\Domain\Object\Skill\Workflow\SkillTemplate;
use Zenstruck\Foundry\Object\Instantiator;
use Zenstruck\Foundry\ObjectFactory;

/**
 * @extends ObjectFactory<SkillTemplate>
 */
final class SkillTemplateFactory extends ObjectFactory
{
    protected function defaults(): array
    {
        return [
            'name'        => self::faker()->words(3, true),
            'content'     => self::faker()->paragraph(2),
            'description' => self::faker()->optional()->paragraph(),
        ];
    }

    public static function class(): string
    {
        return SkillTemplate::class;
    }

    protected function initialize(): static
    {
        return $this->instantiateWith(Instantiator::use(SkillTemplate::create(...)));
    }

    public function withoutDescription(): self
    {
        return $this->with(['description' => null]);
    }

    public function withDescription(): self
    {
        return $this->with(['description' => self::faker()->paragraph()]);
    }
}
