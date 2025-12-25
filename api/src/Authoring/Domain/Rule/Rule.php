<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Domain\Rule;

use Dairectiv\Authoring\Domain\Directive\Directive;
use Dairectiv\Authoring\Domain\Directive\DirectiveId;
use Dairectiv\Authoring\Domain\Directive\Metadata\DirectiveMetadata;

final class Rule extends Directive
{
    public private(set) RuleContent $content;

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
