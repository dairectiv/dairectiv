<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Domain\Rule;

use Dairectiv\Authoring\Domain\Directive\Directive;
use Dairectiv\Authoring\Domain\Directive\DirectiveDescription;
use Dairectiv\Authoring\Domain\Directive\DirectiveId;
use Dairectiv\Authoring\Domain\Directive\DirectiveName;

final class Rule extends Directive
{
    public private(set) RuleContent $content;

    public private(set) RuleExamples $examples;

    public static function draft(
        DirectiveId $id,
        DirectiveName $name,
        DirectiveDescription $description,
        RuleContent $content,
        ?RuleExamples $examples = null,
    ): self {
        $rule = parent::create($id, $name, $description);

        $rule->content = $content;
        $rule->examples = $examples ?? RuleExamples::empty();

        return $rule;
    }

    public function updateContent(
        ?RuleContent $content = null,
        ?RuleExamples $examples = null,
    ): void {
        if (null !== $content) {
            $this->content = $content;
        }

        if (null !== $examples) {
            $this->examples = $examples;
        }

        $this->markContentAsUpdated();
    }
}
