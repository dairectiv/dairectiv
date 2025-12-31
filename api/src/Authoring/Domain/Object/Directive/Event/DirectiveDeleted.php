<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Domain\Object\Directive\Event;

use Dairectiv\Authoring\Domain\Object\Directive\DirectiveId;
use Dairectiv\SharedKernel\Domain\Object\Event\DomainEvent;

final readonly class DirectiveDeleted implements DomainEvent
{
    public function __construct(public DirectiveId $directiveId)
    {
    }
}
