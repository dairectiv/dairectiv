<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Application\Skill\Draft;

use Dairectiv\Authoring\Domain\Object\Skill\Skill;

final readonly class Output
{
    public function __construct(public Skill $skill)
    {
    }
}
