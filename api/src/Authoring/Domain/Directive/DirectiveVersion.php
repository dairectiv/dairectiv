<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Domain\Directive;

final readonly class DirectiveVersion implements \Stringable
{
    private function __construct(public int $version)
    {
    }

    public static function initial(): self
    {
        return new self(1);
    }

    public function increment(): self
    {
        return new self($this->version + 1);
    }

    public function equals(DirectiveVersion $version): bool
    {
        return $this->version === $version->version;
    }

    public function isOlderThan(DirectiveVersion $version): bool
    {
        return $this->version < $version->version;
    }

    public function isNewerThan(DirectiveVersion $version): bool
    {
        return $this->version > $version->version;
    }

    public function __toString(): string
    {
        return \sprintf('v%d', $this->version);
    }
}
