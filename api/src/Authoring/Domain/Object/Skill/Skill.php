<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Domain\Object\Skill;

use Dairectiv\Authoring\Domain\Object\Directive\Directive;
use Dairectiv\Authoring\Domain\Object\Directive\DirectiveId;
use Dairectiv\Authoring\Domain\Object\Directive\Metadata\DirectiveMetadata;
use Dairectiv\Authoring\Domain\Object\Skill\Workflow\SkillWorkflow;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Skill extends Directive
{
    #[ORM\Column(name: 'skill_content', type: 'authoring_skill_content')]
    public private(set) SkillContent $content;

    #[ORM\Column(name: 'skill_workflow', type: 'object_value')]
    public private(set) SkillWorkflow $workflow;

    #[ORM\Column(name: 'skill_examples', type: 'object_value')]
    public private(set) SkillExamples $examples;

    public static function draft(
        DirectiveId $id,
        DirectiveMetadata $metadata,
        SkillContent $content,
        SkillWorkflow $workflow,
        ?SkillExamples $examples = null,
    ): self {
        $skill = new self();

        $skill->content = $content;
        $skill->workflow = $workflow;
        $skill->examples = $examples ?? SkillExamples::empty();

        $skill->initialize($id, $metadata);

        return $skill;
    }

    public function updateContent(
        ?SkillContent $content = null,
        ?SkillWorkflow $workflow = null,
        ?SkillExamples $examples = null,
    ): void {
        if (null !== $content) {
            $this->content = $content;
        }

        if (null !== $workflow) {
            $this->workflow = $workflow;
        }

        if (null !== $examples) {
            $this->examples = $examples;
        }

        $this->markContentAsUpdated();
    }

    public function getCurrentSnapshot(): SkillSnapshot
    {
        return SkillSnapshot::fromSkill($this);
    }
}
