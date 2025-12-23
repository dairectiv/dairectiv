<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Framework;

use Dairectiv\SharedKernel\Application\Command\Command;
use Dairectiv\SharedKernel\Application\Command\CommandBus;
use Dairectiv\SharedKernel\Application\Query\Query;
use Dairectiv\SharedKernel\Application\Query\QueryBus;
use Dairectiv\SharedKernel\Domain\Event\DomainEvent;
use Dairectiv\SharedKernel\Infrastructure\Symfony\Messenger\Transport\TestTransport;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class IntegrationTestCase extends WebTestCase
{
    protected KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        parent::setUp();
    }

    protected function tearDown(): void
    {
        TestTransport::reset();
        parent::tearDown();
    }

    protected function assertPostConditions(): void
    {
        $remainingStack = TestTransport::getStack();
        self::assertEmpty(
            $remainingStack,
            \sprintf('Expected no remaining message to assert, got %d messages.', \count($remainingStack)),
        );
        parent::assertPostConditions();
    }

    /**
     * @template T of object
     * @param class-string<T> $serviceId
     * @return T
     */
    final public static function getService(string $serviceId): object
    {
        $container = static::getContainer();

        $service = $container->get($serviceId);

        self::assertInstanceOf($serviceId, $service);

        return $service;
    }

    final public function execute(Command $command): ?object
    {
        $commandBus = self::getService(CommandBus::class);

        return $commandBus->execute($command);
    }

    final public function fetch(Query $query): object
    {
        $queryBus = self::getService(QueryBus::class);

        return $queryBus->fetch($query);
    }

    /**
     * @param class-string $domainEvent
     */
    final public function assertDomainEventHasBeenDispatched(string $domainEvent, int $times = 1): void
    {
        self::assertIsDomainEvent($domainEvent);
        $countDispatchedDomainEvent = TestTransport::countDispatchedDomainEvent($domainEvent);
        self::assertSame(
            $times,
            $countDispatchedDomainEvent,
            \sprintf(
                'Expected domain event "%s" to be dispatched %d times, but got %d',
                $domainEvent,
                $times,
                $countDispatchedDomainEvent,
            ),
        );
        TestTransport::ackDomainEvent($domainEvent);
    }

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
