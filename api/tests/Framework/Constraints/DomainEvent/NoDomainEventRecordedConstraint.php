<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Framework\Constraints\DomainEvent;

use Dairectiv\SharedKernel\Domain\Object\Event\DomainEventQueue;
use PHPUnit\Framework\Constraint\Constraint;

final class NoDomainEventRecordedConstraint extends Constraint
{
    protected function matches(mixed $other): bool
    {
        return 0 === \count(DomainEventQueue::all());
    }

    public function toString(): string
    {
        return 'no domain events have been recorded and waiting to be dispatched';
    }
}
