<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Infrastructure\Doctrine\DBAL\Types;

use Dairectiv\Authoring\Domain\Object\Skill\SkillContent;
use Dairectiv\SharedKernel\Infrastructure\Doctrine\DBAL\Types\TextValueType;

final class SkillContentType extends TextValueType
{
    protected function getTextValueClass(): string
    {
        return SkillContent::class;
    }
}
