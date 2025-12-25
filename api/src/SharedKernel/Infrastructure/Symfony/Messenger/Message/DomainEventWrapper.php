<?php

declare(strict_types=1);

namespace Dairectiv\SharedKernel\Infrastructure\Symfony\Messenger\Message;

use Dairectiv\SharedKernel\Domain\Object\Event\DomainEvent;
use Symfony\Component\DependencyInjection\Attribute\Exclude;

#[Exclude]
final readonly class DomainEventWrapper
{
    public function __construct(public DomainEvent $domainEvent)
    {
    }
}
