<?php

declare(strict_types=1);

namespace Dairectiv\SharedKernel\Domain\Object\ValueObject;

interface IntValue
{
    public static function fromInt(int $value): static;

    public function toInt(): int;
}
