<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Domain\Directive\Version;

final readonly class VersionNumber implements \Stringable
{
    private function __construct(public int $number)
    {
    }

    public static function initial(): self
    {
        return new self(1);
    }

    public function increment(): self
    {
        return new self($this->number + 1);
    }

    public function equals(VersionNumber $number): bool
    {
        return $this->number === $number->number;
    }

    public function isOlderThan(VersionNumber $number): bool
    {
        return $this->number < $number->number;
    }

    public function isNewerThan(VersionNumber $number): bool
    {
        return $this->number > $number->number;
    }

    public function __toString(): string
    {
        return \sprintf('v%d', $this->number);
    }
}
