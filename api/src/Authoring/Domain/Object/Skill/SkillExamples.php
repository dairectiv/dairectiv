<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Domain\Object\Skill;

use Dairectiv\SharedKernel\Domain\Object\Assert;
use Dairectiv\SharedKernel\Domain\Object\ValueObject\ObjectValue;

/**
 * @implements \IteratorAggregate<int, SkillExample>
 */
final readonly class SkillExamples implements \Countable, \IteratorAggregate, ObjectValue
{
    /**
     * @param list<SkillExample> $examples
     */
    private function __construct(public array $examples)
    {
    }

    /**
     * @param list<SkillExample> $examples
     */
    public static function fromList(array $examples): self
    {
        /** @phpstan-ignore staticMethod.alreadyNarrowedType */
        Assert::allIsInstanceOf($examples, SkillExample::class, 'All examples must be SkillExample instances.');

        return new self($examples);
    }

    public static function empty(): self
    {
        return new self([]);
    }

    public function add(SkillExample $example): self
    {
        return new self([...$this->examples, $example]);
    }

    public function count(): int
    {
        return \count($this->examples);
    }

    public function isEmpty(): bool
    {
        return 0 === $this->count();
    }

    /**
     * @return \Traversable<int, SkillExample>
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->examples);
    }

    /**
     * @template T of mixed
     * @param \Closure(SkillExample): T $callback
     * @return T[]
     */
    public function map(\Closure $callback): array
    {
        return array_map($callback, $this->examples);
    }

    public static function fromArray(array $state): static
    {
        Assert::keyExists($state, 'examples');
        Assert::isArray($state['examples']);
        Assert::allIsArray($state['examples']);

        return new self(array_values(array_map(SkillExample::fromArray(...), $state['examples'])));
    }

    public function toArray(): array
    {
        return [
            'examples' => $this->map(static fn (SkillExample $example): array => $example->toArray()),
        ];
    }
}
