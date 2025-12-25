<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Domain\Rule;

use Dairectiv\SharedKernel\Domain\Assert;

final readonly class RuleDescription implements \Stringable
{
    private function __construct(public string $description)
    {
    }

    public static function fromString(string $description): self
    {
        Assert::notEmpty($description, 'Rule description cannot be empty.');
        Assert::maxLength($description, 255, 'Rule description is too long.');

        return new self($description);
    }

    public function __toString(): string
    {
        return $this->description;
    }
}
