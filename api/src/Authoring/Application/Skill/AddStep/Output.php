<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Application\Skill\AddStep;

use Dairectiv\Authoring\Domain\Object\Skill\Workflow\Step;

final readonly class Output
{
    public function __construct(
        public Step $step,
    ) {
    }
}
