<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Infrastructure\Zenstruck\Foundry\Factory\Rule;

use Dairectiv\Authoring\Domain\Object\Rule\RuleExample;
use Zenstruck\Foundry\Object\Instantiator;
use Zenstruck\Foundry\ObjectFactory;

/**
 * @extends ObjectFactory<RuleExample>
 */
final class RuleExampleFactory extends ObjectFactory
{
    protected function defaults(): array
    {
        return [
            'good'        => self::faker()->paragraph(),
            'bad'         => null,
            'explanation' => self::faker()->optional()->paragraph(),
        ];
    }

    public static function class(): string
    {
        return RuleExample::class;
    }

    protected function initialize(): static
    {
        return $this->instantiateWith(Instantiator::use(RuleExample::create(...)));
    }

    public function good(): self
    {
        return $this->with([
            'good' => self::faker()->paragraph(),
            'bad'  => null,
        ]);
    }

    public function bad(): self
    {
        return $this->with([
            'good' => null,
            'bad'  => self::faker()->paragraph(),
        ]);
    }

    public function transformation(): self
    {
        return $this->with([
            'good' => self::faker()->paragraph(),
            'bad'  => self::faker()->paragraph(),
        ]);
    }

    public function withExplanation(): self
    {
        return $this->with(['explanation' => self::faker()->paragraph()]);
    }

    public function withoutExplanation(): self
    {
        return $this->with(['explanation' => null]);
    }
}
