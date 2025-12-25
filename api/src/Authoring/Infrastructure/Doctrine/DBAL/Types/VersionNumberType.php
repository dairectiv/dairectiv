<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Infrastructure\Doctrine\DBAL\Types;

use Dairectiv\Authoring\Domain\Object\Directive\Version\VersionNumber;
use Dairectiv\SharedKernel\Infrastructure\Doctrine\DBAL\Types\IntValueType;

final class VersionNumberType extends IntValueType
{
    protected function getIntValueClass(): string
    {
        return VersionNumber::class;
    }
}
