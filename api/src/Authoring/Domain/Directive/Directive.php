<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Domain\Directive;

use Cake\Chronos\Chronos;
use Dairectiv\Authoring\Domain\Directive\Event\DirectiveArchived;
use Dairectiv\Authoring\Domain\Directive\Event\DirectiveDrafted;
use Dairectiv\Authoring\Domain\Directive\Event\DirectivePublished;
use Dairectiv\Authoring\Domain\Directive\Event\DirectiveUpdated;
use Dairectiv\Authoring\Domain\Directive\Exception\DirectiveConflictException;
use Dairectiv\SharedKernel\Domain\AggregateRoot;

abstract class Directive extends AggregateRoot
{
    public private(set) DirectiveId $id;

    public private(set) DirectiveState $state;

    public private(set) DirectiveVersion $version;

    public private(set) DirectiveName $name;

    public private(set) Chronos $createdAt;

    public private(set) Chronos $updatedAt;

    final public function __construct()
    {
        $this->createdAt = Chronos::now();
        $this->updatedAt = Chronos::now();
    }

    final public static function create(DirectiveId $id, DirectiveName $name): static
    {
        $directive = new static();

        $directive->id = $id;
        $directive->name = $name;
        $directive->version = DirectiveVersion::initial();
        $directive->state = DirectiveState::Draft;

        $directive->recordEvent(new DirectiveDrafted($directive->id));

        return $directive;
    }

    final protected function checkVersion(DirectiveVersion $expectedVersion): void
    {
        if (!$this->version->equals($expectedVersion)) {
            throw new DirectiveConflictException($expectedVersion, $this);
        }
    }

    final protected function markAsUpdated(): void
    {
        $this->updatedAt = Chronos::now();
        $this->version = $this->version->increment();

        $this->recordEvent(new DirectiveUpdated($this->id, $this->version));
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
