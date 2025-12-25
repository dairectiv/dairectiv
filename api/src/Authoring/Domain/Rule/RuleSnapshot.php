<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Domain\Rule;

use Dairectiv\Authoring\Domain\Directive\Version\VersionSnapshot;

final readonly class RuleSnapshot extends VersionSnapshot
{
    public RuleContent $content;

    public RuleExamples $examples;

    private function __construct(RuleContent $content, RuleExamples $examples)
    {
        $this->content = $content;
        $this->examples = $examples;
    }

    public static function fromRule(Rule $rule): self
    {
        return new self($rule->content, $rule->examples);
    }
}
