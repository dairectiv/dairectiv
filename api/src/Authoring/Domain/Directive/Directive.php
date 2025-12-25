<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Domain\Directive;

use Cake\Chronos\Chronos;
use Dairectiv\Authoring\Domain\Directive\Event\DirectiveArchived;
use Dairectiv\Authoring\Domain\Directive\Event\DirectiveDrafted;
use Dairectiv\Authoring\Domain\Directive\Event\DirectivePublished;
use Dairectiv\Authoring\Domain\Directive\Event\DirectiveUpdated;
use Dairectiv\Authoring\Domain\Directive\Metadata\DirectiveDescription;
use Dairectiv\Authoring\Domain\Directive\Metadata\DirectiveMetadata;
use Dairectiv\Authoring\Domain\Directive\Metadata\DirectiveName;
use Dairectiv\Authoring\Domain\Directive\Version\Version;
use Dairectiv\Authoring\Domain\Directive\Version\VersionSnapshot;
use Dairectiv\SharedKernel\Domain\AggregateRoot;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

abstract class Directive extends AggregateRoot
{
    public private(set) DirectiveId $id;

    public private(set) DirectiveState $state;

    public private(set) DirectiveMetadata $metadata;

    public private(set) Chronos $createdAt;

    public private(set) Chronos $updatedAt;

    public private(set) Version $currentVersion;

    /**
     * @var Collection<int, Version>
     */
    public private(set) Collection $history;

    final public function __construct()
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
        $this->currentVersion = Version::initialize($this);
        $this->history->add($this->currentVersion);

        $this->recordEvent(new DirectiveDrafted($this->id));
    }

    final public function updateMetadata(?DirectiveName $name = null, ?DirectiveDescription $description = null): void
    {
        $this->metadata = $this->metadata->with($name, $description);
        $this->updatedAt = Chronos::now();
    }

    final protected function markContentAsUpdated(): void
    {
        $this->currentVersion = $this->currentVersion->increment();
        $this->updatedAt = Chronos::now();

        $this->recordEvent(new DirectiveUpdated($this->id, $this->currentVersion->number));
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
}
