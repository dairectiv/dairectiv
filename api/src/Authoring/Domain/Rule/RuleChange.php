<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Domain\Rule;

use Dairectiv\Authoring\Domain\ChangeSet\Change;
use Dairectiv\Authoring\Domain\Directive\Directive;
use Dairectiv\Authoring\Domain\Directive\DirectiveVersion;

/**
 * @extends Change<Rule>
 */
final class RuleChange extends Change
{
    // Source snapshot (state before the change was applied)
    public private(set) ?RuleDescription $sourceDescription = null;

    public private(set) ?RuleContent $sourceContent = null;

    public private(set) ?RuleExamples $sourceExamples = null;

    public function __construct(
        DirectiveVersion $sourceVersion,
        // Delta (new values to apply)
        public readonly ?RuleDescription $description = null,
        public readonly ?RuleContent $content = null,
        public readonly ?RuleExamples $examples = null,
    ) {
        parent::__construct($sourceVersion);
    }

    public function captureSourceSnapshot(Directive $directive): void
    {
        $this->sourceDescription = $directive->description;
        $this->sourceContent = $directive->content;
        $this->sourceExamples = $directive->examples;
    }
}
