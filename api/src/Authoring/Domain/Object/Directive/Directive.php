<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Domain\Object\Directive;

use Cake\Chronos\Chronos;
use Dairectiv\Authoring\Domain\Object\Directive\Event\DirectiveArchived;
use Dairectiv\Authoring\Domain\Object\Directive\Event\DirectiveDrafted;
use Dairectiv\Authoring\Domain\Object\Directive\Event\DirectivePublished;
use Dairectiv\Authoring\Domain\Object\Directive\Event\DirectiveUpdated;
use Dairectiv\Authoring\Domain\Object\Rule\Rule;
use Dairectiv\Authoring\Domain\Object\Skill\Skill;
use Dairectiv\SharedKernel\Domain\Object\AggregateRoot;
use Dairectiv\SharedKernel\Domain\Object\Assert;
use Doctrine\DBAL\Types\Types;
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

    #[ORM\Column(type: Types::STRING)]
    public private(set) string $name;

    #[ORM\Column(type: Types::TEXT)]
    public private(set) string $description;

    #[ORM\Column(type: 'chronos')]
    public private(set) Chronos $createdAt;

    #[ORM\Column(type: 'chronos')]
    public private(set) Chronos $updatedAt;

    final protected function initialize(DirectiveId $id, string $name, string $description): void
    {
        $this->id = $id;
        $this->createdAt = Chronos::now();
        $this->updatedAt = Chronos::now();
        $this->name = $name;
        $this->description = $description;
        $this->state = DirectiveState::Draft;

        $this->recordEvent(new DirectiveDrafted($this->id));
    }

    abstract public static function draft(DirectiveId $id, string $name, string $description): Directive;

    final public function updateMetadata(?string $name = null, ?string $description = null): void
    {
        Assert::allNotNull([$name, $description], 'At least one metadata field must be provided.');

        $this->name = $name ?? $this->name;
        $this->description = $description ?? $this->description;
        $this->markAsUpdated();
    }

    final public function markAsUpdated(): void
    {
        $this->assertNotArchived();

        $this->updatedAt = Chronos::now();

        $this->recordEvent(new DirectiveUpdated($this->id));
    }

    final public function publish(): void
    {
        Assert::eq($this->state, DirectiveState::Draft, 'Only draft directives can be published.');

        $this->state = DirectiveState::Published;
        $this->updatedAt = Chronos::now();

        $this->recordEvent(new DirectivePublished($this->id));
    }

    final public function archive(): void
    {
        Assert::notEq($this->state, DirectiveState::Archived, 'Directive is already archived.');

        $this->state = DirectiveState::Archived;
        $this->updatedAt = Chronos::now();

        $this->recordEvent(new DirectiveArchived($this->id));
    }

    final protected function assertNotArchived(): void
    {
        Assert::notEq($this->state, DirectiveState::Archived, 'Cannot perform this action on an archived directive.');
    }
}
