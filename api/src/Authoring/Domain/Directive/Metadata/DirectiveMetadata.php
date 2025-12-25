<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Domain\Directive\Metadata;

final class DirectiveMetadata
{
    public private(set) DirectiveName $name;

    public private(set) DirectiveDescription $description;

    public static function create(DirectiveName $name, DirectiveDescription $description): self
    {
        $metadata = new self();

        $metadata->name = $name;
        $metadata->description = $description;

        return $metadata;
    }

    public function with(?DirectiveName $name, ?DirectiveDescription $description): self
    {
        $metadata = new self();

        $metadata->name = $name ?? $this->name;
        $metadata->description = $description ?? $this->description;

        return $metadata;
    }
}
