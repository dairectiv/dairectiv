<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Domain\Directive\Metadata;

use Dairectiv\SharedKernel\Domain\Assert;

final readonly class DirectiveName implements \Stringable
{
    private function __construct(public string $name)
    {
    }

    public static function fromString(string $name): self
    {
        Assert::notEmpty($name, 'Directive name cannot be empty.');
        Assert::maxLength($name, 255, 'Directive name is too long.');

        return new self($name);
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
