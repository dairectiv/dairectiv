<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Domain\Directive;

use Dairectiv\SharedKernel\Domain\Assert;
use Dairectiv\SharedKernel\Domain\ValueObject\StringValue;

final readonly class DirectiveId implements \Stringable, StringValue
{
    private function __construct(public string $id)
    {
    }

    public static function fromString(string $value): static
    {
        Assert::kebabCase($value, \sprintf('Directive ID "%s" is not in kebab-case.', $value));

        return new self($value);
    }

    public function __toString(): string
    {
        return $this->id;
    }
}
