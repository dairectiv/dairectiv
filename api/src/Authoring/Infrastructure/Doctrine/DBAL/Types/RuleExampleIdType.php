<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Infrastructure\Doctrine\DBAL\Types;

use Dairectiv\Authoring\Domain\Object\Rule\Example\ExampleId;
use Dairectiv\SharedKernel\Infrastructure\Doctrine\DBAL\Types\UuidValueType;

final class RuleExampleIdType extends UuidValueType
{
    protected function getUidClass(): string
    {
        return ExampleId::class;
    }
}
