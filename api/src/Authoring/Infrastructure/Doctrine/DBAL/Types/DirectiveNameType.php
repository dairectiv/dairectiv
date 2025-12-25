<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Infrastructure\Doctrine\DBAL\Types;

use Dairectiv\Authoring\Domain\Object\Directive\Metadata\DirectiveName;
use Dairectiv\SharedKernel\Infrastructure\Doctrine\DBAL\Types\StringValueType;

final class DirectiveNameType extends StringValueType
{
    protected function getStringValueClass(): string
    {
        return DirectiveName::class;
    }
}
