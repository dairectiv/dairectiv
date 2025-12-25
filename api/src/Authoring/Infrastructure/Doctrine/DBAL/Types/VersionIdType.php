<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Infrastructure\Doctrine\DBAL\Types;

use Dairectiv\Authoring\Domain\Directive\Version\VersionId;
use Dairectiv\SharedKernel\Infrastructure\Doctrine\DBAL\Types\StringValueType;

final class VersionIdType extends StringValueType
{
    protected function getStringValueClass(): string
    {
        return VersionId::class;
    }
}
