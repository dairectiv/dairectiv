<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Domain\Directive;

final readonly class DirectiveName implements \Stringable
{
    private function __construct(public string $name)
    {
    }

    public static function fromString(string $name): self
    {
        return new self($name);
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
