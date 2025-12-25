<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Domain\Object\Directive\Version;

use Cake\Chronos\Chronos;
use Dairectiv\Authoring\Domain\Object\Directive\Directive;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'authoring_directive_version')]
class Version
{
    #[ORM\Id]
    #[ORM\Column(type: 'authoring_version_id')]
    public private(set) VersionId $id;

    #[ORM\Column(type: 'authoring_version_number')]
    public private(set) VersionNumber $number;

    #[ORM\ManyToOne(targetEntity: Directive::class, inversedBy: 'history')]
    #[ORM\JoinColumn(nullable: false)]
    public private(set) Directive $directive;

    #[ORM\Column(type: 'object_value')]
    public private(set) VersionSnapshot $snapshot;

    #[ORM\Column(type: 'chronos')]
    public private(set) Chronos $createdAt;

    public function __construct()
    {
        $this->createdAt = Chronos::now();
    }

    public static function initialize(Directive $directive): self
    {
        $snapshot = new self();

        $snapshot->number = VersionNumber::initial();
        $snapshot->id = VersionId::create($directive, $snapshot->number);
        $snapshot->directive = $directive;
        $snapshot->snapshot = $directive->getCurrentSnapshot();

        return $snapshot;
    }

    public function increment(): self
    {
        $newVersion = new self();

        $newVersion->number = $this->number->increment();
        $newVersion->id = VersionId::create($this->directive, $newVersion->number);
        $newVersion->directive = $this->directive;
        $newVersion->snapshot = $this->directive->getCurrentSnapshot();

        return $newVersion;
    }
}
