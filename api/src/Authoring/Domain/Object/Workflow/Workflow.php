<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Domain\Object\Workflow;

use Dairectiv\Authoring\Domain\Object\Directive\Directive;
use Dairectiv\Authoring\Domain\Object\Directive\DirectiveId;
use Dairectiv\Authoring\Domain\Object\Workflow\Example\Example;
use Dairectiv\Authoring\Domain\Object\Workflow\Step\Step;
use Dairectiv\SharedKernel\Domain\Object\Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Workflow extends Directive
{
    #[ORM\Column(name: 'workflow_content', type: Types::TEXT, nullable: true)]
    public private(set) ?string $content = null;

    /**
     * @var Collection<int, Example>
     */
    #[ORM\OneToMany(targetEntity: Example::class, mappedBy: 'workflow', cascade: ['persist'], orphanRemoval: true, fetch: 'EAGER')]
    public private(set) Collection $examples;

    /**
     * @var Collection<int, Step>
     */
    #[ORM\OneToMany(targetEntity: Step::class, mappedBy: 'workflow', cascade: ['persist'], orphanRemoval: true, fetch: 'EAGER')]
    #[ORM\OrderBy(['order' => 'ASC'])]
    public private(set) Collection $steps;

    public function __construct()
    {
        $this->examples = new ArrayCollection();
        $this->steps = new ArrayCollection();
    }

    public static function draft(DirectiveId $id, string $name, string $description): Workflow
    {
        $workflow = new self();

        $workflow->initialize($id, $name, $description);

        return $workflow;
    }

    public function updateContent(string $content): void
    {
        $this->content = self::trim($content);
        $this->markAsUpdated();
    }

    public function addExample(Example $example): void
    {
        $this->examples->add($example);

        $this->markAsUpdated();
    }

    public function removeExample(Example $example): void
    {
        Assert::true($this->examples->contains($example), 'Example does not belong to this workflow.');

        $this->examples->removeElement($example);

        $this->markAsUpdated();
    }

    public function removeStep(Step $step): void
    {
        Assert::true($this->steps->contains($step), 'Step does not belong to this workflow.');

        $removedOrder = $step->order;
        $this->steps->removeElement($step);

        // Reorder remaining steps to fill the gap
        foreach ($this->steps as $s) {
            if ($s->order > $removedOrder) {
                $s->setOrder($s->order - 1);
            }
        }

        $this->markAsUpdated();
    }

    /**
     * @internal Called by Step::create() only
     */
    public function addStep(Step $step, ?Step $after = null): void
    {
        if (null !== $after) {
            Assert::true($this->steps->contains($after), 'Reference step does not belong to this workflow.');
        }

        $newOrder = null === $after ? 1 : $after->order + 1;

        // Shift existing steps to make room
        foreach ($this->steps as $s) {
            if ($s->order >= $newOrder) {
                $s->setOrder($s->order + 1);
            }
        }

        $step->setOrder($newOrder);
        $this->steps->add($step);

        $this->markAsUpdated();
    }

    /**
     * Move a step after another step, or to the beginning if $after is null.
     */
    public function moveStepAfter(Step $stepToMove, ?Step $after = null): void
    {
        $this->assertNotArchived();
        Assert::true($this->steps->contains($stepToMove), 'Step does not belong to this workflow.');

        if (null !== $after) {
            Assert::true($this->steps->contains($after), 'Reference step does not belong to this workflow.');
        }

        $currentOrder = $stepToMove->order;
        $newOrder = null === $after ? 1 : $after->order + 1;

        // If moving after itself or already in correct position, do nothing
        if ($after === $stepToMove || $currentOrder === $newOrder) {
            return;
        }

        // Adjust newOrder if moving forward (after reference step will shift)
        if ($currentOrder < $newOrder) {
            --$newOrder;
        }

        if ($currentOrder < $newOrder) {
            // Moving forward: decrement orders between current+1 and new
            foreach ($this->steps as $s) {
                if ($s->order > $currentOrder && $s->order <= $newOrder) {
                    $s->setOrder($s->order - 1);
                }
            }
        } else {
            // Moving backward: increment orders between new and current-1
            foreach ($this->steps as $s) {
                if ($s->order >= $newOrder && $s->order < $currentOrder) {
                    $s->setOrder($s->order + 1);
                }
            }
        }

        $stepToMove->setOrder($newOrder);
        $this->markAsUpdated();
    }
}
