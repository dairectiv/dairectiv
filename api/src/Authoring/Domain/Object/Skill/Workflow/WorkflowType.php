<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Domain\Object\Skill\Workflow;

enum WorkflowType: string
{
    case Sequential = 'sequential';
    case Template = 'template';
    case Checklist = 'checklist';
    case Hybrid = 'hybrid';
}
