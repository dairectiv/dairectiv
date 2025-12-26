<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Domain\Object\Skill;

use Dairectiv\Authoring\Domain\Object\Directive\Version\VersionSnapshot;
use Dairectiv\Authoring\Domain\Object\Skill\Workflow\SkillWorkflow;
use Dairectiv\SharedKernel\Domain\Object\Assert;
use Dairectiv\SharedKernel\Domain\Object\ValueObject\ObjectValue;

final readonly class SkillSnapshot extends VersionSnapshot implements ObjectValue
{
    public SkillContent $content;

    public SkillWorkflow $workflow;

    public SkillExamples $examples;

    private function __construct(SkillContent $content, SkillWorkflow $workflow, SkillExamples $examples)
    {
        $this->content = $content;
        $this->workflow = $workflow;
        $this->examples = $examples;
    }

    public static function fromSkill(Skill $skill): self
    {
        return new self($skill->content, $skill->workflow, $skill->examples);
    }

    public static function fromArray(array $state): static
    {
        Assert::keyExists($state, 'content');
        Assert::string($state['content']);

        Assert::keyExists($state, 'workflow');
        Assert::isArray($state['workflow']);

        Assert::keyExists($state, 'examples');
        Assert::isArray($state['examples']);

        return new self(
            SkillContent::fromString($state['content']),
            SkillWorkflow::fromArray($state['workflow']),
            SkillExamples::fromArray($state['examples']),
        );
    }

    public function toArray(): array
    {
        return [
            'content'  => (string) $this->content,
            'workflow' => $this->workflow->toArray(),
            'examples' => $this->examples->toArray(),
        ];
    }
}
