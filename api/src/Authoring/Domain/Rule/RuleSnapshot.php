<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Domain\Rule;

use Dairectiv\Authoring\Domain\Directive\Version\VersionSnapshot;
use Dairectiv\SharedKernel\Domain\Assert;
use Dairectiv\SharedKernel\Domain\ValueObject\ObjectValue;

final readonly class RuleSnapshot extends VersionSnapshot implements ObjectValue
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

    public static function fromArray(array $state): static
    {
        Assert::keyExists($state, 'content');
        Assert::string($state['content']);
        Assert::keyExists($state, 'examples');
        Assert::isArray($state['examples']);

        return new self(
            RuleContent::fromString($state['content']),
            RuleExamples::fromArray($state['examples']),
        );
    }

    public function toArray(): array
    {
        return [
            'content'  => (string) $this->content,
            'examples' => $this->examples->toArray(),
        ];
    }
}
