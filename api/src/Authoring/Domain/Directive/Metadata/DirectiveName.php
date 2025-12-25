<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Domain\Directive\Metadata;

use Dairectiv\SharedKernel\Domain\Assert;
use Dairectiv\SharedKernel\Domain\ValueObject\StringValue;

final readonly class DirectiveName implements \Stringable, StringValue
{
    private function __construct(public string $name)
    {
    }

    public static function fromString(string $value): static
    {
        Assert::notEmpty($value, 'Directive name cannot be empty.');
        Assert::maxLength($value, 255, 'Directive name is too long.');

        return new self($value);
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
