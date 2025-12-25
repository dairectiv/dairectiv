<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Domain\Object\Rule;

use Dairectiv\Authoring\Domain\Object\Directive\Directive;
use Dairectiv\Authoring\Domain\Object\Directive\DirectiveId;
use Dairectiv\Authoring\Domain\Object\Directive\Metadata\DirectiveMetadata;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Rule extends Directive
{
    #[ORM\Column(type: 'authoring_rule_content')]
    public private(set) RuleContent $content;

    #[ORM\Column(type: 'object_value')]
    public private(set) RuleExamples $examples;

    public static function draft(
        DirectiveId $id,
        DirectiveMetadata $metadata,
        RuleContent $content,
        ?RuleExamples $examples = null,
    ): self {
        $rule = new self();

        $rule->content = $content;
        $rule->examples = $examples ?? RuleExamples::empty();

        $rule->initialize($id, $metadata);

        return $rule;
    }

    public function updateContent(?RuleContent $content = null, ?RuleExamples $examples = null): void
    {
        if (null !== $content) {
            $this->content = $content;
        }

        if (null !== $examples) {
            $this->examples = $examples;
        }

        $this->markContentAsUpdated();
    }

    public function getCurrentSnapshot(): RuleSnapshot
    {
        return RuleSnapshot::fromRule($this);
    }
}
