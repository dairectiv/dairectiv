<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Infrastructure\Doctrine\DBAL\Types;

use Dairectiv\Authoring\Domain\Directive\Metadata\DirectiveDescription;
use Dairectiv\SharedKernel\Infrastructure\Doctrine\DBAL\Types\TextValueType;

final class DirectiveDescriptionType extends TextValueType
{
    protected function getTextValueClass(): string
    {
        return DirectiveDescription::class;
    }
}
