<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Domain\Object\Skill\Workflow;

enum StepType: string
{
    case Action = 'action';
    case Decision = 'decision';
    case Template = 'template';
    case Validation = 'validation';
}
