<?php

declare(strict_types=1);

namespace Dairectiv\SharedKernel\Domain\Object;

/**
 * Trait for normalizing string properties in domain entities.
 *
 * Provides helper methods to trim whitespace from strings on entity creation/update.
 */
trait StringNormalizer
{
    /**
     * Trims leading and trailing whitespace from a string.
     */
    protected static function trim(string $value): string
    {
        return trim($value);
    }

    /**
     * Trims leading and trailing whitespace from a nullable string.
     * Returns null if the input is null.
     */
    protected static function trimOrNull(?string $value): ?string
    {
        return null === $value ? null : trim($value);
    }
}
