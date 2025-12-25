<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Framework;

use Cake\Chronos\Chronos;
use Dairectiv\SharedKernel\Application\Command\Command;
use Dairectiv\SharedKernel\Application\Command\CommandBus;
use Dairectiv\SharedKernel\Application\Query\Query;
use Dairectiv\SharedKernel\Application\Query\QueryBus;
use Dairectiv\SharedKernel\Domain\Object\Event\DomainEventQueue;
use Dairectiv\SharedKernel\Infrastructure\Symfony\Messenger\Transport\TestTransport;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class IntegrationTestCase extends WebTestCase
{
    use ReflectionAssertions;

    protected KernelBrowser $client;

    protected function setUp(): void
    {
        Chronos::setTestNow(Chronos::now());
        $this->client = static::createClient();
        parent::setUp();
    }

    protected function tearDown(): void
    {
        DomainEventQueue::reset();
        self::getService(TestTransport::class)->reset();
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

    final public function getEntityManager(): EntityManagerInterface
    {
        return self::getService(EntityManagerInterface::class);
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

    final public function assertConvertToDatabaseValue(mixed $expectedDatabaseValue, mixed $value, string $type): void
    {
        $convertedValue = $this->getEntityManager()->getConnection()->convertToDatabaseValue($value, $type);

        self::assertEquals($expectedDatabaseValue, $convertedValue);
    }

    final public function assertConvertToPhpValue(mixed $expectedPhpValue, mixed $value, string $type): void
    {
        $convertedValue = $this->getEntityManager()->getConnection()->convertToPHPValue($value, $type);

        self::assertEquals($expectedPhpValue, $convertedValue);
    }

    /**
     * @template T of object
     * @param class-string<T> $entityClass
     * @param array<string, mixed> $criteria
     * @return ($strict is true ? T : T|null)
     */
    final public function findEntity(string $entityClass, array $criteria = [], bool $strict = false): ?object
    {
        $entityManager = self::getService(EntityManagerInterface::class);

        $entity = $entityManager->getRepository($entityClass)->findOneBy($criteria);

        if ($strict) {
            self::assertNotNull($entity);
        }

        if (null === $entity) {
            return null;
        }

        self::assertInstanceOf($entityClass, $entity);

        return $entity;
    }

    /**
     * @template T of object
     * @param class-string<T> $entityClass
     * @param array<string, mixed> $criteria
     * @return T[]
     */
    final public function findEntities(string $entityClass, array $criteria = []): array
    {
        $entityManager = self::getService(EntityManagerInterface::class);

        $entities = $entityManager->getRepository($entityClass)->findBy($criteria);

        self::assertContainsOnlyInstancesOf($entityClass, $entities);

        return $entities;
    }
}
