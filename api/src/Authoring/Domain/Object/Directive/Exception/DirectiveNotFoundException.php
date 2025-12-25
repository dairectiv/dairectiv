<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Domain\Object\Directive\Exception;

use Dairectiv\Authoring\Domain\Object\Directive\DirectiveId;
use Dairectiv\SharedKernel\Domain\Object\Exception\EntityNotFoundException;

final class DirectiveNotFoundException extends EntityNotFoundException
{
    public static function fromId(DirectiveId $id): self
    {
        return new self(\sprintf('Directive with ID %s not found.', $id));
    }
}
