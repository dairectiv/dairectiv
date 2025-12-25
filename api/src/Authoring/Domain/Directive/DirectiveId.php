<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Domain\Directive;

use Dairectiv\SharedKernel\Domain\Assert;

final readonly class DirectiveId implements \Stringable
{
    private function __construct(public string $id)
    {
    }

    public static function fromString(string $id): DirectiveId
    {
        Assert::kebabCase($id, \sprintf('Directive ID "%s" is not in kebab-case.', $id));

        return new self($id);
    }

    public function __toString(): string
    {
        return $this->id;
    }
}
