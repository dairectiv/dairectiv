<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Unit\Authoring\Domain\Object\Skill;

use Dairectiv\Authoring\Domain\Object\Directive\DirectiveId;
use Dairectiv\Authoring\Domain\Object\Directive\Metadata\DirectiveDescription;
use Dairectiv\Authoring\Domain\Object\Directive\Metadata\DirectiveMetadata;
use Dairectiv\Authoring\Domain\Object\Directive\Metadata\DirectiveName;
use Dairectiv\Authoring\Domain\Object\Skill\Skill;
use Dairectiv\Authoring\Domain\Object\Skill\SkillContent;
use Dairectiv\Authoring\Domain\Object\Skill\SkillExample;
use Dairectiv\Authoring\Domain\Object\Skill\SkillExamples;
use Dairectiv\Authoring\Domain\Object\Skill\SkillSnapshot;
use Dairectiv\Authoring\Domain\Object\Skill\Workflow\SequentialWorkflow;
use Dairectiv\Authoring\Domain\Object\Skill\Workflow\SkillStep;
use Dairectiv\Authoring\Domain\Object\Skill\Workflow\WorkflowType;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
#[Group('authoring')]
final class SkillSnapshotTest extends TestCase
{
    public function testItShouldCreateSnapshotFromSkill(): void
    {
        $content = SkillContent::fromString('Skill content');
        $workflow = SequentialWorkflow::create([
            SkillStep::action(1, 'Step', 'Content'),
        ]);
        $examples = SkillExamples::fromList([
            SkillExample::create('Scenario', 'Input', 'Output'),
        ]);

        $skill = Skill::draft(
            DirectiveId::fromString('skill-id'),
            DirectiveMetadata::create(
                DirectiveName::fromString('Skill Name'),
                DirectiveDescription::fromString('Description'),
            ),
            $content,
            $workflow,
            $examples,
        );

        $snapshot = SkillSnapshot::fromSkill($skill);

        self::assertSame($content, $snapshot->content);
        self::assertSame($workflow, $snapshot->workflow);
        self::assertSame($examples, $snapshot->examples);
    }

    public function testItShouldConvertToArray(): void
    {
        $content = SkillContent::fromString('Skill content');
        $workflow = SequentialWorkflow::create([
            SkillStep::action(1, 'Step', 'Content'),
        ]);
        $examples = SkillExamples::fromList([
            SkillExample::create('Scenario', 'Input', 'Output'),
        ]);

        $skill = Skill::draft(
            DirectiveId::fromString('skill-id'),
            DirectiveMetadata::create(
                DirectiveName::fromString('Skill Name'),
                DirectiveDescription::fromString('Description'),
            ),
            $content,
            $workflow,
            $examples,
        );

        $array = $skill->getCurrentSnapshot()->toArray();

        self::assertSame('Skill content', $array['content']);
        self::assertArrayHasKey('workflow', $array);
        self::assertIsArray($array['workflow']);
        self::assertSame('sequential', $array['workflow']['type']);
        self::assertArrayHasKey('examples', $array);
    }

    public function testItShouldCreateFromArray(): void
    {
        $snapshot = SkillSnapshot::fromArray([
            'content'  => 'Skill content',
            'workflow' => [
                'type'  => 'sequential',
                'steps' => [
                    ['order' => 1, 'title' => 'Step', 'content' => 'Content', 'type' => 'action'],
                ],
            ],
            'examples' => [
                'examples' => [
                    ['scenario' => 'Scenario', 'input' => 'Input', 'output' => 'Output'],
                ],
            ],
        ]);

        self::assertSame('Skill content', (string) $snapshot->content);
        self::assertSame(WorkflowType::Sequential, $snapshot->workflow->getType());
        self::assertCount(1, $snapshot->examples);
    }
}
