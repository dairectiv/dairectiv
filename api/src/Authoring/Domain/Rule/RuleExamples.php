<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Domain\Rule;

use Dairectiv\SharedKernel\Domain\Assert;

/**
 * @implements \IteratorAggregate<int, RuleExample>
 */
final readonly class RuleExamples implements \Countable, \IteratorAggregate
{
    /**
     * @param list<RuleExample> $examples
     */
    private function __construct(public array $examples)
    {
    }

    /**
     * @param list<RuleExample> $examples
     */
    public static function fromArray(array $examples): self
    {
        /** @phpstan-ignore staticMethod.alreadyNarrowedType */
        Assert::allIsInstanceOf($examples, RuleExample::class, 'All examples must be RuleExample instances.');

        return new self($examples);
    }

    public static function empty(): self
    {
        return new self([]);
    }

    public function add(RuleExample $example): self
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
     * @return \Traversable<int, RuleExample>
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->examples);
    }

    /**
     * @return list<RuleExample>
     */
    public function goods(): array
    {
        return array_values(array_filter($this->examples, static fn (RuleExample $example): bool => $example->hasGood() && !$example->hasBad()));
    }

    /**
     * @return list<RuleExample>
     */
    public function bads(): array
    {
        return array_values(array_filter($this->examples, static fn (RuleExample $example): bool => $example->hasBad() && !$example->hasGood()));
    }

    /**
     * @return list<RuleExample>
     */
    public function transformations(): array
    {
        return array_values(array_filter($this->examples, static fn (RuleExample $example): bool => $example->isTransformation()));
    }
}
