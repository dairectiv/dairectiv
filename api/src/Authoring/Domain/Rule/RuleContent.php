<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Domain\Rule;

use Dairectiv\SharedKernel\Domain\Assert;

final readonly class RuleContent implements \Stringable
{
    private function __construct(public string $content)
    {
    }

    public static function fromString(string $content): self
    {
        Assert::notEmpty($content, 'Rule content cannot be empty.');

        return new self($content);
    }

    public function __toString(): string
    {
        return $this->content;
    }
}
