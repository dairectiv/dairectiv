<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Domain\Directive\Version;

use Dairectiv\Authoring\Domain\Directive\Directive;
use Dairectiv\SharedKernel\Domain\ValueObject\StringValue;

final readonly class VersionId implements \Stringable, StringValue
{
    private function __construct(public string $id)
    {
    }

    public static function create(Directive $directive, VersionNumber $versionNumber): self
    {
        return new self(\sprintf('%s-%s', $directive->id, $versionNumber));
    }

    public function equals(self $other): bool
    {
        return $this->id === $other->id;
    }

    public static function fromString(string $value): static
    {
        return new self($value);
    }

    public function __toString(): string
    {
        return $this->id;
    }
}
