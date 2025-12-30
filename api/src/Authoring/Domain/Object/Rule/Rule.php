<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Domain\Object\Rule;

use Dairectiv\Authoring\Domain\Object\Directive\Directive;
use Dairectiv\Authoring\Domain\Object\Directive\DirectiveId;
use Dairectiv\Authoring\Domain\Object\Rule\Example\Example;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Rule extends Directive
{
    #[ORM\Column(name: 'rule_content', type: Types::TEXT, nullable: true)]
    public private(set) ?string $content = null;

    /**
     * @var Collection<int, Example>
     */
    #[ORM\OneToMany(targetEntity: Example::class, mappedBy: 'rule', cascade: ['persist'])]
    public private(set) Collection $examples;

    public function __construct()
    {
        $this->examples = new ArrayCollection();
    }

    public static function draft(DirectiveId $id, string $name, string $description): Rule
    {
        $rule = new self();

        $rule->initialize($id, $name, $description);

        return $rule;
    }

    public function updateContent(string $content): void
    {
        $this->content = $content;
        $this->markAsUpdated();
    }

    public function addExample(?string $good, ?string $bad, ?string $explanation): void
    {
        $this->examples->add(
            Example::create(
                $this,
                $good,
                $bad,
                $explanation,
            ),
        );

        $this->markAsUpdated();
    }
}
