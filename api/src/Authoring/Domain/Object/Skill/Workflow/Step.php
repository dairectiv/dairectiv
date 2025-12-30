<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Domain\Object\Skill\Workflow;

use Cake\Chronos\Chronos;
use Dairectiv\Authoring\Domain\Object\Skill\Skill;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'authoring_skill_step')]
class Step
{
    #[ORM\Id]
    #[ORM\Column(type: 'authoring_skill_step_id')]
    public private(set) StepId $id;

    #[ORM\Column(type: 'chronos')]
    public private(set) Chronos $createdAt;

    #[ORM\Column(type: 'chronos')]
    public private(set) Chronos $updatedAt;

    #[ORM\Column(type: Types::INTEGER)]
    public private(set) int $order;

    #[ORM\Column(type: Types::TEXT)]
    public private(set) string $content;

    #[ORM\ManyToOne(targetEntity: Skill::class, inversedBy: 'steps')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    public private(set) Skill $skill;

    private function __construct()
    {
        $this->createdAt = Chronos::now();
        $this->updatedAt = Chronos::now();
    }

    public static function create(Skill $skill, string $content, ?self $after = null): self
    {
        $step = new self();

        $step->id = StepId::generate();
        $step->skill = $skill;
        $step->content = $content;
        $step->skill->addStep($step, $after);

        return $step;
    }

    public function update(string $content): void
    {
        $this->content = $content;
        $this->updatedAt = Chronos::now();
        $this->skill->markAsUpdated();
    }

    /**
     * @internal Called by Skill aggregate only
     */
    public function setOrder(int $order): void
    {
        $this->order = $order;
    }
}
