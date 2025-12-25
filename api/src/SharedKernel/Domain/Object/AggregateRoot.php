<?php

declare(strict_types=1);

namespace Dairectiv\SharedKernel\Domain\Object;

use Dairectiv\SharedKernel\Domain\Object\Event\DomainEvent;
use Dairectiv\SharedKernel\Domain\Object\Event\DomainEventQueue;

abstract class AggregateRoot
{
    protected function recordEvent(DomainEvent $event): void
    {
        DomainEventQueue::recordEvent($event);
    }
}
