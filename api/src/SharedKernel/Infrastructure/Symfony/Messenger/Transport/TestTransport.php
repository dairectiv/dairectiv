<?php

declare(strict_types=1);

namespace Dairectiv\SharedKernel\Infrastructure\Symfony\Messenger\Transport;

use Dairectiv\SharedKernel\Domain\Event\DomainEvent;
use Dairectiv\SharedKernel\Infrastructure\Symfony\Messenger\Message\DomainEventWrapper;
use Symfony\Component\DependencyInjection\Attribute\When;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\TransportInterface;

#[When('test')]
final class TestTransport implements TransportInterface
{
    /**
     * @var Envelope[]
     */
    private static array $stack = [];

    public static function reset(): void
    {
        self::$stack = [];
    }

    /**
     * @return Envelope[]
     */
    public static function getStack(): array
    {
        return self::$stack;
    }

    /**
     * @param class-string<DomainEvent> $eventName
     */
    public static function countDispatchedDomainEvent(string $eventName): int
    {
        $dispatched = 0;

        foreach (self::$stack as $stack) {
            $message = $stack->getMessage();

            if (
                $message instanceof DomainEventWrapper
                && $message->domainEvent instanceof $eventName
            ) {
                ++$dispatched;
            }
        }

        return $dispatched;
    }

    /**
     * @param class-string<DomainEvent> $eventName
     */
    public static function ackDomainEvent(string $eventName): void
    {
        foreach (self::$stack as $k => $envelope) {
            $message = $envelope->getMessage();

            if (
                $message instanceof DomainEventWrapper
                && $message->domainEvent instanceof $eventName
            ) {
                unset(self::$stack[$k]);
            }
        }

        self::$stack = array_values(self::$stack);
    }

    /**
     * @codeCoverageIgnore
     */
    public function get(): iterable
    {
        return [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function ack(Envelope $envelope): void
    {
    }

    /**
     * @codeCoverageIgnore
     */
    public function reject(Envelope $envelope): void
    {
    }

    public function send(Envelope $envelope): Envelope
    {
        self::$stack[] = $envelope;

        return $envelope;
    }
}
