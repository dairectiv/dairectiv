<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Domain\Directive\Event;

use Dairectiv\Authoring\Domain\Directive\DirectiveId;
use Dairectiv\Authoring\Domain\Directive\Version\VersionNumber;
use Dairectiv\SharedKernel\Domain\Event\DomainEvent;

final readonly class DirectiveUpdated implements DomainEvent
{
    public function __construct(public DirectiveId $directiveId, public VersionNumber $versionNumber)
    {
    }
}
