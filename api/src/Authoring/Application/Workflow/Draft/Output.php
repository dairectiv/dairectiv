<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Application\Workflow\Draft;

use Dairectiv\Authoring\Domain\Object\Workflow\Workflow;

final readonly class Output
{
    public function __construct(public Workflow $workflow)
    {
    }
}
