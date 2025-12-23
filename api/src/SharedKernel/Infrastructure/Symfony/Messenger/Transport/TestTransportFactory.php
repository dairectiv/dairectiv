<?php

declare(strict_types=1);

namespace Dairectiv\SharedKernel\Infrastructure\Symfony\Messenger\Transport;

use Symfony\Component\DependencyInjection\Attribute\When;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\TransportFactoryInterface;

/**
 * @implements TransportFactoryInterface<TestTransport>
 */
#[When('test')]
final readonly class TestTransportFactory implements TransportFactoryInterface
{
    /**
     * @param array<array-key, mixed> $options
     */
    public function createTransport(string $dsn, array $options, SerializerInterface $serializer): TestTransport
    {
        return new TestTransport();
    }

    /**
     * @param array<array-key, mixed> $options
     */
    public function supports(string $dsn, array $options): bool
    {
        return str_starts_with($dsn, 'test://');
    }
}
