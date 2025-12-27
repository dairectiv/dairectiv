<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Infrastructure\Zenstruck\Foundry\Dto;

use Cake\Chronos\Chronos;
use Dairectiv\Authoring\Domain\Object\Directive\DirectiveId;
use Dairectiv\Authoring\Domain\Object\Directive\Metadata\DirectiveDescription;
use Dairectiv\Authoring\Domain\Object\Directive\Metadata\DirectiveMetadata;
use Dairectiv\Authoring\Domain\Object\Directive\Metadata\DirectiveName;
use Dairectiv\Authoring\Domain\Object\Skill\Skill;
use Dairectiv\Authoring\Domain\Object\Skill\SkillContent;
use Dairectiv\Authoring\Domain\Object\Skill\SkillExample;
use Dairectiv\Authoring\Domain\Object\Skill\SkillExamples;
use Dairectiv\Authoring\Domain\Object\Skill\Workflow\SkillWorkflow;

final class SkillDto
{
    public string $id;

    public string $name;

    public string $description;

    public string $content;

    public Chronos $createdAt;

    public Chronos $updatedAt;

    public SkillWorkflow $workflow;

    /**
     * @var list<SkillExample>
     */
    public array $examples;

    public function build(): Skill
    {
        $now = Chronos::now();
        Chronos::setTestNow($this->createdAt);

        $skill = Skill::draft(
            DirectiveId::fromString($this->id),
            DirectiveMetadata::create(
                DirectiveName::fromString($this->name),
                DirectiveDescription::fromString($this->description),
            ),
            SkillContent::fromString($this->content),
            $this->workflow,
            SkillExamples::fromList($this->examples),
        );

        Chronos::setTestNow($now);

        return $skill;
    }
}
