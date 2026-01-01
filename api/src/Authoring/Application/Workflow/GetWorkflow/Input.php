<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Application\Workflow\GetWorkflow;

use Dairectiv\SharedKernel\Application\Query\Query;

final readonly class Input implements Query
{
    public function __construct(public string $id)
    {
    }
}
