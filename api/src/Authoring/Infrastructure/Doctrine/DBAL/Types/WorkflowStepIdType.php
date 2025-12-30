<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Infrastructure\Doctrine\DBAL\Types;

use Dairectiv\Authoring\Domain\Object\Workflow\Step\StepId;
use Dairectiv\SharedKernel\Infrastructure\Doctrine\DBAL\Types\UuidValueType;

final class WorkflowStepIdType extends UuidValueType
{
    protected function getUidClass(): string
    {
        return StepId::class;
    }
}
