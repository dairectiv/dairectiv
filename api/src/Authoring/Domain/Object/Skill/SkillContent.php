<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Domain\Object\Skill;

use Dairectiv\SharedKernel\Domain\Object\Assert;
use Dairectiv\SharedKernel\Domain\Object\ValueObject\TextValue;

final readonly class SkillContent implements \Stringable, TextValue
{
    private function __construct(public string $content)
    {
    }

    public static function fromString(string $value): static
    {
        Assert::notEmpty($value, 'Skill content cannot be empty.');

        return new self($value);
    }

    public function __toString(): string
    {
        return $this->content;
    }
}
