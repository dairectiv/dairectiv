<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Domain\Directive\Metadata;

use Dairectiv\SharedKernel\Domain\Assert;
use Dairectiv\SharedKernel\Domain\ValueObject\TextValue;

final readonly class DirectiveDescription implements \Stringable, TextValue
{
    private function __construct(public string $description)
    {
    }

    public static function fromString(string $value): static
    {
        Assert::notEmpty($value, 'Directive description cannot be empty.');
        Assert::maxLength($value, 500, 'Directive description is too long, max length: 500.');

        return new self($value);
    }

    public function __toString(): string
    {
        return $this->description;
    }
}
