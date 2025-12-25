<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Framework;

use Dairectiv\SharedKernel\Domain\Object\Event\DomainEvent;

trait ReflectionAssertions
{
    /**
     * @param class-string|object $actual
     * @phpstan-assert DomainEvent|class-string<DomainEvent> $actual
     */
    final public static function assertIsDomainEvent(string | object $actual): void
    {
        if (\is_object($actual)) {
            $actual = $actual::class;
        }

        self::assertTrue(
            is_subclass_of($actual, DomainEvent::class),
            \sprintf('Expected instance of "%s" but got "%s"', DomainEvent::class, $actual),
        );
    }
}
