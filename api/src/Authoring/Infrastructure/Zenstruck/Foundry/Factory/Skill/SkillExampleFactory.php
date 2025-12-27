<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Infrastructure\Zenstruck\Foundry\Factory\Skill;

use Dairectiv\Authoring\Domain\Object\Skill\SkillExample;
use Zenstruck\Foundry\Object\Instantiator;
use Zenstruck\Foundry\ObjectFactory;

/**
 * @extends ObjectFactory<SkillExample>
 */
final class SkillExampleFactory extends ObjectFactory
{
    protected function defaults(): array
    {
        return [
            'scenario'    => self::faker()->sentence(),
            'input'       => self::faker()->paragraph(),
            'output'      => self::faker()->paragraph(),
            'explanation' => self::faker()->optional()->paragraph(),
        ];
    }

    public static function class(): string
    {
        return SkillExample::class;
    }

    protected function initialize(): static
    {
        return $this->instantiateWith(Instantiator::use(SkillExample::create(...)));
    }

    public function withoutExplanation(): self
    {
        return $this->with(['explanation' => null]);
    }

    public function withExplanation(): self
    {
        return $this->with(['explanation' => self::faker()->paragraph()]);
    }
}
