<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Domain\Rule;

use Dairectiv\SharedKernel\Domain\Assert;
use Dairectiv\SharedKernel\Domain\ValueObject\TextValue;

final readonly class RuleContent implements \Stringable, TextValue
{
    private function __construct(public string $content)
    {
    }

    public static function fromString(string $value): static
    {
        Assert::notEmpty($value, 'Rule content cannot be empty.');

        return new self($value);
    }

    public function __toString(): string
    {
        return $this->content;
    }
}
