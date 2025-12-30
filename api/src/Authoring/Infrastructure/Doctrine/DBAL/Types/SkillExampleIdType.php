<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Infrastructure\Doctrine\DBAL\Types;

use Dairectiv\Authoring\Domain\Object\Skill\Example\ExampleId;
use Dairectiv\SharedKernel\Infrastructure\Doctrine\DBAL\Types\UuidValueType;

final class SkillExampleIdType extends UuidValueType
{
    protected function getUidClass(): string
    {
        return ExampleId::class;
    }
}
