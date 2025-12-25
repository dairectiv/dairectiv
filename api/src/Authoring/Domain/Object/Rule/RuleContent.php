<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Domain\Object\Rule;

use Dairectiv\SharedKernel\Domain\Object\Assert;
use Dairectiv\SharedKernel\Domain\Object\ValueObject\TextValue;

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
