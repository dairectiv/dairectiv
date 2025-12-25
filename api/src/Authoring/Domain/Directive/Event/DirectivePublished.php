<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Domain\Directive\Event;

use Dairectiv\Authoring\Domain\Directive\DirectiveId;
use Dairectiv\SharedKernel\Domain\Event\DomainEvent;

final readonly class DirectivePublished implements DomainEvent
{
    public function __construct(public DirectiveId $directiveId)
    {
    }
}
