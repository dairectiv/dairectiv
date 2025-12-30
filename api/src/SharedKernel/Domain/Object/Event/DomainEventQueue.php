<?php

declare(strict_types=1);

namespace Dairectiv\SharedKernel\Domain\Object\Event;

final class DomainEventQueue
{
    private static ?self $instance = null;

    /**
     * @var DomainEvent[]
     */
    private array $domainEvents = [];

    public static function reset(): void
    {
        self::$instance = null;
    }

    private static function getInstance(): self
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public static function recordEvent(DomainEvent $event): void
    {
        $instance = self::getInstance();
        $instance->domainEvents[] = $event;
    }

    /**
     * @return iterable<DomainEvent>
     */
    public static function pullEvents(): iterable
    {
        $instance = self::getInstance();
        while ($event = array_shift($instance->domainEvents)) {
            yield $event;
        }
    }

    /**
     * @return DomainEvent[]
     */
    public static function all(): array
    {
        $instance = self::getInstance();

        return $instance->domainEvents;
    }

    public static function markAsDispatched(DomainEvent $event): void
    {
        $instance = self::getInstance();

        foreach ($instance->domainEvents as $key => $domainEvent) {
            if ($domainEvent === $event) {
                unset($instance->domainEvents[$key]);

                return;
            }
        }
    }
}
