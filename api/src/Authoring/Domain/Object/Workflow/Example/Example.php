<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Domain\Object\Workflow\Example;

use Cake\Chronos\Chronos;
use Dairectiv\Authoring\Domain\Object\Workflow\Workflow;
use Dairectiv\SharedKernel\Domain\Object\Assert;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'authoring_workflow_example')]
class Example
{
    #[ORM\Id]
    #[ORM\Column(type: 'authoring_workflow_example_id')]
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

    #[ORM\ManyToOne(targetEntity: Workflow::class, inversedBy: 'examples')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    public private(set) Workflow $workflow;

    private function __construct()
    {
        $this->createdAt = Chronos::now();
        $this->updatedAt = Chronos::now();
    }

    public static function create(
        Workflow $workflow,
        string $scenario,
        string $input,
        string $output,
        ?string $explanation = null,
    ): self {
        $example = new self();

        $example->id = ExampleId::generate();
        $example->workflow = $workflow;
        $example->scenario = $scenario;
        $example->input = $input;
        $example->output = $output;
        $example->explanation = $explanation;
        $example->workflow->addExample($example);

        return $example;
    }

    public function update(
        ?string $scenario = null,
        ?string $input = null,
        ?string $output = null,
        ?string $explanation = null,
    ): void {
        Assert::true(
            null !== $scenario || null !== $input || null !== $output || null !== $explanation,
            'At least one field must be provided.',
        );

        $this->scenario = $scenario ?? $this->scenario;
        $this->input = $input ?? $this->input;
        $this->output = $output ?? $this->output;
        $this->explanation = $explanation ?? $this->explanation;
        $this->updatedAt = Chronos::now();

        $this->workflow->markAsUpdated();
    }
}
