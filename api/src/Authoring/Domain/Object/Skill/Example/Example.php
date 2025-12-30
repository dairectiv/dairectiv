<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Domain\Object\Skill\Example;

use Cake\Chronos\Chronos;
use Dairectiv\Authoring\Domain\Object\Skill\Skill;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'authoring_skill_example')]
class Example
{
    #[ORM\Id]
    #[ORM\Column(type: 'authoring_skill_example_id')]
    public private(set) ExampleId $id;

    #[ORM\Column(type: 'chronos')]
    public private(set) Chronos $createdAt;

    #[ORM\Column(type: 'chronos')]
    public private(set) Chronos $updatedAt;

    #[ORM\Column(type: Types::TEXT)]
    public private(set) string $scenario;

    #[ORM\Column(type: Types::TEXT)]
    public private(set) string $input;

    #[ORM\Column(type: Types::TEXT)]
    public private(set) string $output;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    public private(set) ?string $explanation = null;

    #[ORM\ManyToOne(targetEntity: Skill::class, inversedBy: 'examples')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    public private(set) Skill $skill;

    private function __construct()
    {
        $this->createdAt = Chronos::now();
        $this->updatedAt = Chronos::now();
    }

    public static function create(
        Skill $skill,
        string $scenario,
        string $input,
        string $output,
        ?string $explanation = null,
    ): self {
        $example = new self();

        $example->id = ExampleId::generate();
        $example->skill = $skill;
        $example->scenario = $scenario;
        $example->input = $input;
        $example->output = $output;
        $example->explanation = $explanation;
        $example->skill->addExample($example);

        return $example;
    }
}
