<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Fixtures\Domain;

use Dairectiv\SharedKernel\Domain\Object\Event\DomainEvent;

final readonly class FakeEvent implements DomainEvent
{
    public function __construct(public string $foo)
    {
    }
}
