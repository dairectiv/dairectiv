<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Domain\Object\Skill\Workflow;

enum StepType: string
{
    case Action = 'action';
    case Decision = 'decision';
    case Template = 'template';
    case Validation = 'validation';

    /**
     * @return array<value-of<self::*>>
     */
    public static function values(): array
    {
        return array_map(static fn (self $type): string => $type->value, self::cases());
    }
}
