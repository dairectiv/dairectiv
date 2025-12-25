<?php

declare(strict_types=1);

namespace Dairectiv\SharedKernel\Domain\Object\ValueObject;

interface StringValue extends \Stringable
{
    public static function fromString(string $value): static;
}
