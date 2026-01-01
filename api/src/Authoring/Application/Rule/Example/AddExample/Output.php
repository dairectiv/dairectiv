<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Application\Rule\Example\AddExample;

use Dairectiv\Authoring\Domain\Object\Rule\Example\Example;

final readonly class Output
{
    public function __construct(
        public Example $example,
    ) {
    }
}
