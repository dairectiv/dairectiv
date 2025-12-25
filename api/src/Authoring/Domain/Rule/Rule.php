<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Domain\Rule;

use Dairectiv\Authoring\Domain\ChangeSet\Change;
use Dairectiv\Authoring\Domain\Directive\Directive;
use Dairectiv\Authoring\Domain\Directive\DirectiveId;
use Dairectiv\Authoring\Domain\Directive\DirectiveName;

/**
 * @extends Directive<RuleChange>
 */
final class Rule extends Directive
{
    public private(set) RuleDescription $description;

    public private(set) RuleContent $content;

    public private(set) RuleExamples $examples;

    public static function draft(
        DirectiveId $id,
        DirectiveName $name,
        RuleDescription $description,
        RuleContent $content,
        ?RuleExamples $examples = null,
    ): self {
        $rule = parent::create($id, $name);

        $rule->description = $description;
        $rule->content = $content;
        $rule->examples = $examples ?? RuleExamples::empty();

        return $rule;
    }

    protected function doApplyChanges(Change $change): void
    {
        if (null !== $change->description) {
            $this->description = $change->description;
        }

        if (null !== $change->content) {
            $this->content = $change->content;
        }

        if (null !== $change->examples) {
            $this->examples = $change->examples;
        }
    }
}
