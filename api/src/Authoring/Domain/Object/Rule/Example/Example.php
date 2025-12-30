<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Domain\Object\Rule\Example;

use Cake\Chronos\Chronos;
use Dairectiv\Authoring\Domain\Object\Rule\Rule;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'authoring_rule_example')]
class Example
{
    #[ORM\Id]
    #[ORM\Column(type: 'authoring_rule_example_id')]
    public private(set) ExampleId $id;

    #[ORM\Column(type: 'chronos')]
    public private(set) Chronos $createdAt;

    #[ORM\Column(type: 'chronos')]
    public private(set) Chronos $updatedAt;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    public private(set) ?string $good = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    public private(set) ?string $bad = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    public private(set) ?string $explanation = null;

    #[ORM\ManyToOne(targetEntity: Rule::class, inversedBy: 'examples')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    public private(set) Rule $rule;

    private function __construct()
    {
        $this->createdAt = Chronos::now();
        $this->updatedAt = Chronos::now();
    }

    public static function create(
        Rule $rule,
        ?string $good = null,
        ?string $bad = null,
        ?string $explanation = null,
    ): self {
        $example = new self();

        $example->id = ExampleId::generate();
        $example->rule = $rule;
        $example->good = $good;
        $example->bad = $bad;
        $example->explanation = $explanation;
        $example->rule->addExample($example);

        return $example;
    }

    public function update(
        ?string $good = null,
        ?string $bad = null,
        ?string $explanation = null,
    ): void {
        $this->good = $good ?? $this->good;
        $this->bad = $bad ?? $this->bad;
        $this->explanation = $explanation ?? $this->explanation;
        $this->updatedAt = Chronos::now();

        $this->rule->markAsUpdated();
    }
}
