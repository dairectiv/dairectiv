<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Framework;

use Cake\Chronos\Chronos;
use Dairectiv\SharedKernel\Domain\Event\DomainEvent;
use Dairectiv\SharedKernel\Domain\Event\DomainEventQueue;

trait AggregateRootAssertions
{
    /**
     * @var array<int, true>
     */
    private array $assertedEventIds = [];

    private bool $noEventsAsserted = false;

    protected function setUp(): void
    {
        $this->assertedEventIds = [];
        $this->noEventsAsserted = false;
        DomainEventQueue::reset();
        Chronos::setTestNow(Chronos::now());
    }

    protected function tearDown(): void
    {
        DomainEventQueue::reset();
        Chronos::setTestNow();
    }

    protected function assertPostConditions(): void
    {
        if ($this->noEventsAsserted) {
            parent::assertPostConditions();

            return;
        }

        $events = DomainEventQueue::all();
        $unassertedEvents = [];

        foreach ($events as $event) {
            if (!isset($this->assertedEventIds[spl_object_id($event)])) {
                $unassertedEvents[] = $event::class;
            }
        }

        if (\count($unassertedEvents) > 0) {
            self::fail(\sprintf(
                'The following domain events were recorded but not asserted: %s. You must assert all domain events or use assertNoDomainEvents() if none are expected.',
                implode(', ', $unassertedEvents),
            ));
        }

        parent::assertPostConditions();
    }

    /**
     * @template T of DomainEvent
     *
     * @param class-string<T> $domainEvent
     *
     * @return T
     */
    final protected function assertDomainEventRecorded(string $domainEvent): DomainEvent
    {
        $events = DomainEventQueue::all();

        foreach ($events as $event) {
            if ($event instanceof $domainEvent && !isset($this->assertedEventIds[spl_object_id($event)])) {
                $this->assertedEventIds[spl_object_id($event)] = true;

                return $event;
            }
        }

        self::fail(\sprintf(
            'Expected domain event of type "%s" to be recorded, but it was not found.',
            $domainEvent,
        ));
    }

    /**
     * Assert that no domain events were recorded.
     */
    final protected function assertNoDomainEvents(): void
    {
        $events = DomainEventQueue::all();
        self::assertCount(0, $events, 'Expected no domain events, but some were found.');
        $this->noEventsAsserted = true;
    }

    /**
     * Reset the domain event queue and tracked assertions.
     * Use this when you need to clear events mid-test (e.g., after setup operations).
     */
    final protected function resetDomainEvents(): void
    {
        DomainEventQueue::reset();
        $this->assertedEventIds = [];
        $this->noEventsAsserted = false;
    }
}
