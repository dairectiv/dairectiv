<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Infrastructure\Doctrine\DBAL\Types;

use Dairectiv\Authoring\Domain\Object\Directive\DirectiveId;
use Dairectiv\SharedKernel\Infrastructure\Doctrine\DBAL\Types\StringValueType;

final class DirectiveIdType extends StringValueType
{
    protected function getStringValueClass(): string
    {
        return DirectiveId::class;
    }
}
