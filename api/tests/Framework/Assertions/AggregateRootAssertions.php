<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Framework\Assertions;

use Dairectiv\SharedKernel\Domain\Object\Event\DomainEvent;
use Dairectiv\SharedKernel\Domain\Object\Event\DomainEventQueue;
use Dairectiv\Tests\Framework\Constraints\DomainEvent\DomainEventRecordedConstraint;
use Dairectiv\Tests\Framework\Constraints\DomainEvent\NoDomainEventRecordedConstraint;

trait AggregateRootAssertions
{
    private bool $domainEventsAsserted = false;

    protected function setUp(): void
    {
        $this->resetDomainEvents();
    }

    protected function tearDown(): void
    {
        self::assertNoDomainEvents();

        parent::tearDown();
    }

    /**
     * @template T of DomainEvent
     *
     * @param class-string<T> $domainEvent
     */
    final protected function assertDomainEventRecorded(string $domainEvent, int $count = 1): void
    {
        self::assertThat($domainEvent, new DomainEventRecordedConstraint($count));
        $this->domainEventsAsserted = true;
    }

    /**
     * Assert that no domain events were recorded.
     */
    final protected function assertNoDomainEvents(): void
    {
        self::assertThat(null, new NoDomainEventRecordedConstraint());
        $this->domainEventsAsserted = true;
    }

    /**
     * Reset the domain event queue and tracked assertions.
     * Use this when you need to clear events mid-test (e.g., after setup operations).
     */
    final protected function resetDomainEvents(): void
    {
        DomainEventQueue::reset();
        $this->domainEventsAsserted = false;
    }
}
