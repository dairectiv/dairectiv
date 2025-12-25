<?php

declare(strict_types=1);

namespace Dairectiv\SharedKernel\Domain\ValueObject;

interface ObjectValue
{
    /**
     * @param array<array-key, mixed> $state
     */
    public static function fromArray(array $state): static;

    /**
     * @return array<array-key, mixed>
     */
    public function toArray(): array;
}
