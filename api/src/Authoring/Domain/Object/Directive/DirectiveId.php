<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Domain\Object\Directive;

use Dairectiv\SharedKernel\Domain\Object\Assert;
use Dairectiv\SharedKernel\Domain\Object\ValueObject\StringValue;

final readonly class DirectiveId extends StringValue
{
    public static function validate(string $value): void
    {
        Assert::maxLength($value, 120, \sprintf('Directive ID "%s" exceeds maximum length of 120 characters.', $value));
        Assert::kebabCase($value, \sprintf('Directive ID "%s" is not in kebab-case.', $value));
    }
}
