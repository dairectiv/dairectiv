<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Domain\Object\Workflow\Step;

use Cake\Chronos\Chronos;
use Dairectiv\Authoring\Domain\Object\Workflow\Workflow;
use Dairectiv\SharedKernel\Domain\Object\Assert;
use Dairectiv\SharedKernel\Domain\Object\StringNormalizer;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'authoring_workflow_step')]
class Step
{
    use StringNormalizer;

    #[ORM\Id]
    #[ORM\Column(type: 'authoring_workflow_step_id')]
    public private(set) StepId $id;

    #[ORM\Column(type: 'chronos')]
    public private(set) Chronos $createdAt;

    #[ORM\Column(type: 'chronos')]
    public private(set) Chronos $updatedAt;

    #[ORM\Column(name: 'step_order', type: Types::INTEGER)]
    public private(set) int $order;

    #[ORM\Column(type: Types::TEXT)]
    public private(set) string $content;

    #[ORM\ManyToOne(targetEntity: Workflow::class, inversedBy: 'steps')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    public private(set) Workflow $workflow;

    private function __construct()
    {
        $this->createdAt = Chronos::now();
        $this->updatedAt = Chronos::now();
    }

    public static function create(Workflow $workflow, string $content, ?self $after = null): self
    {
        $step = new self();

        $step->id = StepId::generate();
        $step->workflow = $workflow;
        $step->content = self::trim($content);
        $step->workflow->addStep($step, $after);

        return $step;
    }

    public function update(?string $content): void
    {
        Assert::true(
            null !== $content,
            'At least one field must be provided.',
        );

        $this->content = self::trim($content);
        $this->updatedAt = Chronos::now();
        $this->workflow->markAsUpdated();
    }

    /**
     * @internal Called by Workflow aggregate only
     */
    public function setOrder(int $order): void
    {
        $this->order = $order;
    }
}
