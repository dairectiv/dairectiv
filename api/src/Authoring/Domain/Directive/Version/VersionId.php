<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Domain\Directive\Version;

use Dairectiv\Authoring\Domain\Directive\Directive;

final readonly class VersionId implements \Stringable
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

    public function __toString(): string
    {
        return $this->id;
    }
}
