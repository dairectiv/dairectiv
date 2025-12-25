<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Fixtures\Domain;

use Dairectiv\SharedKernel\Domain\Object\AggregateRoot;

final class FakeAggregate extends AggregateRoot
{
    public function __construct(public readonly string $foo)
    {
        $this->recordEvent(new FakeEvent($this->foo));
    }
}
