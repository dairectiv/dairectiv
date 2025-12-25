<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Application\Rule\Draft;

use Dairectiv\Authoring\Domain\Object\Rule\Rule;

final readonly class Output
{
    public function __construct(public Rule $rule)
    {
    }
}
