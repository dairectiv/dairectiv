<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Framework\Constraints\DomainEvent;

use Dairectiv\SharedKernel\Domain\Object\Event\DomainEvent;
use Dairectiv\SharedKernel\Domain\Object\Event\DomainEventQueue;
use PHPUnit\Framework\Constraint\Constraint;

final class DomainEventRecordedConstraint extends Constraint
{
    public function __construct(private readonly int $count)
    {
    }

    protected function matches(mixed $other): bool
    {
        if (
            !\is_string($other)
            || !class_exists($other)
            || !is_subclass_of($other, DomainEvent::class)
        ) {
            return false;
        }

        $recordedEvents = array_filter(
            DomainEventQueue::all(),
            static fn (DomainEvent $event): bool => $event instanceof $other,
        );

        if (\count($recordedEvents) !== $this->count) {
            return false;
        }

        foreach ($recordedEvents as $event) {
            DomainEventQueue::markAsDispatched($event);
        }

        return true;
    }

    public function toString(): string
    {
        return 'has been recorded';
    }
}
