<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Domain\Object\Workflow\Example;

use Cake\Chronos\Chronos;
use Dairectiv\Authoring\Domain\Object\Workflow\Workflow;
use Dairectiv\SharedKernel\Domain\Object\Assert;
use Dairectiv\SharedKernel\Domain\Object\StringNormalizer;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'authoring_workflow_example')]
class Example
{
    use StringNormalizer;

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
        $example->scenario = self::trim($scenario);
        $example->input = self::trim($input);
        $example->output = self::trim($output);
        $example->explanation = self::trimOrNull($explanation);
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

        $this->scenario = self::trimOrNull($scenario) ?? $this->scenario;
        $this->input = self::trimOrNull($input) ?? $this->input;
        $this->output = self::trimOrNull($output) ?? $this->output;
        $this->explanation = self::trimOrNull($explanation) ?? $this->explanation;
        $this->updatedAt = Chronos::now();

        $this->workflow->markAsUpdated();
    }
}
