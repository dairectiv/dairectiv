<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Framework;

use Cake\Chronos\Chronos;
use Coduo\PHPMatcher\PHPUnit\PHPMatcherConstraint;
use Dairectiv\SharedKernel\Application\Command\Command;
use Dairectiv\SharedKernel\Application\Command\CommandBus;
use Dairectiv\SharedKernel\Application\Query\Query;
use Dairectiv\SharedKernel\Application\Query\QueryBus;
use Dairectiv\SharedKernel\Domain\Object\Event\DomainEventQueue;
use Dairectiv\SharedKernel\Infrastructure\Symfony\Messenger\Message\DomainEventWrapper;
use Dairectiv\SharedKernel\Infrastructure\Symfony\Messenger\Transport\TestTransport;
use Dairectiv\Tests\Framework\Helpers\AuthoringHelpers;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\Constraint\Constraint;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Envelope;

abstract class IntegrationTestCase extends WebTestCase
{
    use AuthoringHelpers;
    use ReflectionAssertions;

    private static ?Generator $faker = null;

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
            \sprintf(
                "Expected no remaining message to assert, got %d messages:\n%s",
                \count($remainingStack),
                implode(
                    "\n",
                    array_map(
                        static function (Envelope $envelope): string {
                            $message = $envelope->getMessage();

                            if ($message instanceof DomainEventWrapper) {
                                return $message->domainEvent::class;
                            }

                            return $message::class;
                        },
                        $remainingStack,
                    ),
                ),
            ),
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

    /**
     * @param array<array-key, mixed> $json
     */
    final public function postJson(string $uri, array $json = []): void
    {
        $this->client->request(
            'POST',
            $uri,
            server: [
                'CONTENT_TYPE'        => 'application/json',
                'HTTP_ACCEPT'         => 'application/json',
            ],
            content: \Safe\json_encode($json, \JSON_THROW_ON_ERROR),
        );
    }

    final public function getJson(string $uri): void
    {
        $this->client->request(
            'GET',
            $uri,
            server: [
                'CONTENT_TYPE'        => 'application/json',
                'HTTP_ACCEPT'         => 'application/json',
            ],
        );
    }

    final public function execute(Command $command): ?object
    {
        DomainEventQueue::reset();
        $commandBus = self::getService(CommandBus::class);

        $output = $commandBus->execute($command);

        $this->getEntityManager()->clear();

        return $output;
    }

    final public function fetch(Query $query): object
    {
        DomainEventQueue::reset();
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
    final public static function assertDomainEventHasBeenDispatched(string $domainEvent, int $times = 1): void
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

    /**
     * @template T of object
     * @param T $entity
     * @return T
     */
    final public function persistEntity(object $entity): object
    {
        $entityManager = self::getService(EntityManagerInterface::class);

        $entityManager->persist($entity);
        $entityManager->flush();

        return $entity;
    }

    /**
     * @param array<array-key, mixed> $base
     * @param array<array-key, mixed> $overrides
     * @return array<array-key, mixed>
     */
    public static function override(array $base, array $overrides): array
    {
        $mergeRecursive = function (array $a, array $b) use (&$mergeRecursive): array {
            foreach ($b as $key => $value) {
                if (\is_array($value) && isset($a[$key]) && \is_array($a[$key])) {
                    $a[$key] = $mergeRecursive($a[$key], $value);
                } else {
                    $a[$key] = $value;
                }
            }

            return $a;
        };

        return $mergeRecursive($base, $overrides);
    }

    public static function faker(): Generator
    {
        return self::$faker ??= Factory::create();
    }

    public static function assertThatForResponseContent(Constraint $constraint, string $message = ''): void
    {
        $response = self::getClient()?->getResponse();

        self::assertInstanceOf(Response::class, $response);

        $content = $response->getContent();

        self::assertNotFalse($content);

        self::assertThat($content, $constraint, $message);
    }

    /**
     * @param array<array{propertyPath: string, title: string}> $expectedViolations
     */
    public static function assertUnprocessableResponse(array $expectedViolations): void
    {
        self::assertResponseIsUnprocessable();

        $pattern = [
            'detail'     => '@string@',
            'status'     => 422,
            'title'      => 'Validation Failed',
            'type'       => '@string@',
            'class'      => '@string@',
            'trace'      => '@array@',
            'violations' => array_map(
                static fn (array $violation): array => ['parameters' => '@array@', 'template' => '@string@', 'type' => '@string@.optional()', ...$violation],
                $expectedViolations,
            ),
        ];

        self::assertResponseReturnsJson($pattern);
    }

    /**
     * @param array<array-key, mixed> $expectedJson
     */
    public static function assertResponseReturnsJson(array $expectedJson): void
    {
        self::assertThatForResponseContent(new PHPMatcherConstraint(\Safe\json_encode($expectedJson, \JSON_THROW_ON_ERROR)));
    }
}
