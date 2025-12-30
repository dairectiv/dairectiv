<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Domain\Object\Rule\Exception;

use Dairectiv\Authoring\Domain\Object\Directive\DirectiveId;
use Dairectiv\SharedKernel\Domain\Object\Exception\EntityNotFoundException;

final class RuleNotFoundException extends EntityNotFoundException
{
    public static function fromId(DirectiveId $id): self
    {
        return new self(\sprintf('Rule with ID %s not found.', $id));
    }
}
