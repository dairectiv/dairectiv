<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Domain\Directive\Exception;

use Dairectiv\Authoring\Domain\Directive\Directive;
use Dairectiv\Authoring\Domain\Directive\DirectiveVersion;
use Dairectiv\SharedKernel\Domain\Exception\DomainException;

final class DirectiveConflictException extends DomainException
{
    /** @phpstan-ignore missingType.generics */
    public function __construct(DirectiveVersion $expected, Directive $directive)
    {
        $message = \sprintf(
            'Directive "%s" version conflict: expected version "%s", but got version "%s".',
            $directive->id,
            $expected,
            $directive->version,
        );

        parent::__construct($message);
    }
}
