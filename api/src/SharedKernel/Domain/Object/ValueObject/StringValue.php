<?php

declare(strict_types=1);

namespace Dairectiv\SharedKernel\Domain\Object\ValueObject;

abstract readonly class StringValue implements \Stringable
{
    final private function __construct(public protected(set) string $value)
    {
    }

    abstract protected static function validate(string $value): void;

    final public static function fromString(string $value): static
    {
        static::validate($value);

        return new static($value);
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
