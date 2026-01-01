<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Application\Workflow\Example\AddExample;

use Dairectiv\Authoring\Domain\Object\Workflow\Example\Example;

final readonly class Output
{
    public function __construct(public Example $example)
    {
    }
}
