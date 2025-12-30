<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Application\Skill\AddExample;

use Dairectiv\Authoring\Domain\Object\Skill\Example\Example;

final readonly class Output
{
    public function __construct(public Example $example)
    {
    }
}
