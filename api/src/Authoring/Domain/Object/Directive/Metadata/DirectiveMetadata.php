<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Domain\Object\Directive\Metadata;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
final class DirectiveMetadata
{
    #[ORM\Column(type: 'authoring_directive_name')]
    public private(set) DirectiveName $name;

    #[ORM\Column(type: 'authoring_directive_description')]
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
