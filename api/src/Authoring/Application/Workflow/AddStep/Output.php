<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Application\Workflow\AddStep;

use Dairectiv\Authoring\Domain\Object\Workflow\Step\Step;

final readonly class Output
{
    public function __construct(
        public Step $step,
    ) {
    }
}
