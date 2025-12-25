<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Infrastructure\Doctrine\DBAL\Types;

use Dairectiv\Authoring\Domain\Rule\RuleContent;
use Dairectiv\SharedKernel\Infrastructure\Doctrine\DBAL\Types\TextValueType;

final class RuleContentType extends TextValueType
{
    protected function getTextValueClass(): string
    {
        return RuleContent::class;
    }
}
