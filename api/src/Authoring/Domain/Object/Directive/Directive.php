<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Domain\Object\Directive;

use Cake\Chronos\Chronos;
use Dairectiv\Authoring\Domain\Object\Directive\Event\DirectiveArchived;
use Dairectiv\Authoring\Domain\Object\Directive\Event\DirectiveDrafted;
use Dairectiv\Authoring\Domain\Object\Directive\Event\DirectivePublished;
use Dairectiv\Authoring\Domain\Object\Directive\Event\DirectiveUpdated;
use Dairectiv\Authoring\Domain\Object\Directive\Metadata\DirectiveDescription;
use Dairectiv\Authoring\Domain\Object\Directive\Metadata\DirectiveMetadata;
use Dairectiv\Authoring\Domain\Object\Directive\Metadata\DirectiveName;
use Dairectiv\Authoring\Domain\Object\Directive\Version\Version;
use Dairectiv\Authoring\Domain\Object\Directive\Version\VersionSnapshot;
use Dairectiv\Authoring\Domain\Object\Rule\Rule;
use Dairectiv\Authoring\Domain\Object\Skill\Skill;
use Dairectiv\SharedKernel\Domain\Object\AggregateRoot;
use Dairectiv\SharedKernel\Domain\Object\Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'authoring_directive')]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'discr', type: 'string')]
#[ORM\DiscriminatorMap(['rule' => Rule::class, 'skill' => Skill::class])]
abstract class Directive extends AggregateRoot
{
    #[ORM\Id]
    #[ORM\Column(type: 'authoring_directive_id')]
    public private(set) DirectiveId $id;

    #[ORM\Column(type: 'string', enumType: DirectiveState::class)]
    public private(set) DirectiveState $state;

    #[ORM\Embedded(class: DirectiveMetadata::class, columnPrefix: false)]
    public private(set) DirectiveMetadata $metadata;

    #[ORM\Column(type: 'chronos')]
    public private(set) Chronos $createdAt;

    #[ORM\Column(type: 'chronos')]
    public private(set) Chronos $updatedAt;

    /**
     * @var Collection<int, Version>
     */
    #[ORM\OneToMany(targetEntity: Version::class, mappedBy: 'directive', cascade: ['persist'])]
    #[ORM\OrderBy(['number' => 'DESC'])]
    public private(set) Collection $history;

    public function __construct()
    {
        $this->createdAt = Chronos::now();
        $this->updatedAt = Chronos::now();
        $this->history = new ArrayCollection();
    }

    abstract public function getCurrentSnapshot(): VersionSnapshot;

    final protected function initialize(DirectiveId $id, DirectiveMetadata $metadata): void
    {
        $this->id = $id;
        $this->metadata = $metadata;
        $this->state = DirectiveState::Draft;
        $this->history->add(Version::initialize($this));

        $this->recordEvent(new DirectiveDrafted($this->id));
    }

    final public function updateMetadata(?DirectiveName $name = null, ?DirectiveDescription $description = null): void
    {
        $this->metadata = $this->metadata->with($name, $description);
        $this->updatedAt = Chronos::now();
    }

    final protected function markContentAsUpdated(): void
    {
        $this->updatedAt = Chronos::now();
        $newVersion = $this->getCurrentVersion()->increment();
        $this->history->add($newVersion);

        $this->recordEvent(new DirectiveUpdated($this->id, $newVersion->number));
    }

    final public function publish(): void
    {
        $this->state = DirectiveState::Published;
        $this->updatedAt = Chronos::now();

        $this->recordEvent(new DirectivePublished($this->id));
    }

    final public function archive(): void
    {
        $this->state = DirectiveState::Archived;
        $this->updatedAt = Chronos::now();

        $this->recordEvent(new DirectiveArchived($this->id));
    }

    final public function getCurrentVersion(): Version
    {
        $currentVersion = $this->history->reduce(
            static fn (?Version $current, Version $version): Version => null === $current || $version->number->number > $current->number->number
                ? $version
                : $current,
        );

        Assert::isInstanceOf($currentVersion, Version::class, 'Directive has no versions.');

        return $currentVersion;
    }
}
