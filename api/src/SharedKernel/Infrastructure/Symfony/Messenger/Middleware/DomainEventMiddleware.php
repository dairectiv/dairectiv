<?php

declare(strict_types=1);

namespace Dairectiv\SharedKernel\Infrastructure\Symfony\Messenger\Middleware;

use Dairectiv\SharedKernel\Domain\Event\DomainEventQueue;
use Dairectiv\SharedKernel\Infrastructure\Symfony\Messenger\Message\DomainEventWrapper;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\DispatchAfterCurrentBusStamp;
use Symfony\Component\Messenger\Stamp\TransportNamesStamp;

final readonly class DomainEventMiddleware implements MiddlewareInterface
{
    public function __construct(private MessageBusInterface $eventBus)
    {
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        try {
            $envelope = $stack->next()->handle($envelope, $stack);

            foreach (DomainEventQueue::pullEvents() as $event) {
                $this->eventBus->dispatch(
                    new Envelope(new DomainEventWrapper($event))
                        ->with(
                            new DispatchAfterCurrentBusStamp(),
                            new TransportNamesStamp(['events']),
                        ),
                );
            }
        } catch (\Throwable $e) {
            DomainEventQueue::reset();

            throw $e;
        }

        return $envelope;
    }
}
