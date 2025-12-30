<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Domain\Object\Workflow\Exception;

use Dairectiv\Authoring\Domain\Object\Directive\DirectiveId;
use Dairectiv\SharedKernel\Domain\Object\Exception\EntityNotFoundException;

final class WorkflowNotFoundException extends EntityNotFoundException
{
    public static function fromId(DirectiveId $id): self
    {
        return new self(\sprintf('Workflow with ID %s not found.', $id));
    }
}
