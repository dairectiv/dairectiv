<?php

declare(strict_types=1);

namespace Dairectiv\SharedKernel\Domain;

use Dairectiv\SharedKernel\Domain\Event\DomainEvent;
use Dairectiv\SharedKernel\Domain\Event\DomainEventQueue;

abstract class AggregateRoot
{
    protected function recordEvent(DomainEvent $event): void
    {
        DomainEventQueue::recordEvent($event);
    }
}
