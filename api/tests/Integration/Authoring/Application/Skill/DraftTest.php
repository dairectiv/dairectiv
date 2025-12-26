<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\Authoring\Application\Skill;

use Dairectiv\Authoring\Application\Skill\Draft\Input;
use Dairectiv\Authoring\Application\Skill\Draft\Output;
use Dairectiv\Authoring\Domain\Object\Directive\DirectiveId;
use Dairectiv\Authoring\Domain\Object\Directive\Event\DirectiveDrafted;
use Dairectiv\Authoring\Domain\Object\Directive\Metadata\DirectiveDescription;
use Dairectiv\Authoring\Domain\Object\Directive\Metadata\DirectiveMetadata;
use Dairectiv\Authoring\Domain\Object\Directive\Metadata\DirectiveName;
use Dairectiv\Authoring\Domain\Object\Skill\Skill;
use Dairectiv\Authoring\Domain\Object\Skill\SkillContent;
use Dairectiv\Authoring\Domain\Object\Skill\SkillExamples;
use Dairectiv\Authoring\Domain\Object\Skill\Workflow\ChecklistWorkflow;
use Dairectiv\Authoring\Domain\Object\Skill\Workflow\HybridWorkflow;
use Dairectiv\Authoring\Domain\Object\Skill\Workflow\SequentialWorkflow;
use Dairectiv\Authoring\Domain\Object\Skill\Workflow\SkillWorkflow;
use Dairectiv\Authoring\Domain\Object\Skill\Workflow\TemplateWorkflow;
use Dairectiv\Tests\Framework\IntegrationTestCase;

final class DraftTest extends IntegrationTestCase
{
    public function testItShouldDraftSkillWithSequentialWorkflow(): void
    {
        $output = $this->execute(
            new Input(
                'skill-sequential',
                'Git Commit Skill',
                'Creates structured git commit messages',
                '## When to Use\nUse this skill when committing changes.',
                [
                    'type'  => 'sequential',
                    'steps' => [
                        ['order' => 1, 'title' => 'Gather Context', 'content' => 'Run git status and git diff', 'type' => 'action'],
                        ['order' => 2, 'title' => 'Analyze Changes', 'content' => 'Determine commit type', 'type' => 'decision'],
                        ['order' => 3, 'title' => 'Create Commit', 'content' => 'Execute git commit', 'type' => 'action'],
                    ],
                ],
                [
                    ['scenario' => 'Feature commit', 'input' => 'New login form', 'output' => 'feat: add login form'],
                ],
            ),
        );

        self::assertInstanceOf(Output::class, $output);
        $skill = $this->findEntity(Skill::class, ['id' => DirectiveId::fromString('skill-sequential')], true);

        self::assertEquals($output->skill, $skill);
        $this->assertSkill(
            $skill,
            'Git Commit Skill',
            'Creates structured git commit messages',
            '## When to Use\nUse this skill when committing changes.',
            SequentialWorkflow::class,
            1,
        );

        self::assertDomainEventHasBeenDispatched(DirectiveDrafted::class);
    }

    public function testItShouldDraftSkillWithTemplateWorkflow(): void
    {
        $output = $this->execute(
            new Input(
                'skill-template',
                'Aggregate Root Skill',
                'Creates DDD aggregate roots',
                '## When to Use\nUse when implementing new aggregates.',
                [
                    'type'      => 'template',
                    'templates' => [
                        ['name' => 'Entity', 'content' => '<?php class Entity {}'],
                        ['name' => 'Repository', 'content' => '<?php interface Repository {}'],
                    ],
                ],
            ),
        );

        self::assertInstanceOf(Output::class, $output);
        $skill = $this->findEntity(Skill::class, ['id' => DirectiveId::fromString('skill-template')], true);

        self::assertEquals($output->skill, $skill);
        $this->assertSkill(
            $skill,
            'Aggregate Root Skill',
            'Creates DDD aggregate roots',
            '## When to Use\nUse when implementing new aggregates.',
            TemplateWorkflow::class,
            0,
        );

        self::assertDomainEventHasBeenDispatched(DirectiveDrafted::class);
    }

    public function testItShouldDraftSkillWithChecklistWorkflow(): void
    {
        $output = $this->execute(
            new Input(
                'skill-checklist',
                'Code Review Skill',
                'Guides code review process',
                '## When to Use\nUse when reviewing pull requests.',
                [
                    'type'  => 'checklist',
                    'items' => [
                        ['order' => 1, 'title' => 'Check Tests', 'content' => 'Verify all tests pass', 'type' => 'validation'],
                        ['order' => 2, 'title' => 'Review Logic', 'content' => 'Check business logic', 'type' => 'action'],
                    ],
                ],
            ),
        );

        self::assertInstanceOf(Output::class, $output);
        $skill = $this->findEntity(Skill::class, ['id' => DirectiveId::fromString('skill-checklist')], true);

        self::assertEquals($output->skill, $skill);
        $this->assertSkill(
            $skill,
            'Code Review Skill',
            'Guides code review process',
            '## When to Use\nUse when reviewing pull requests.',
            ChecklistWorkflow::class,
            0,
        );

        self::assertDomainEventHasBeenDispatched(DirectiveDrafted::class);
    }

    public function testItShouldDraftSkillWithHybridWorkflow(): void
    {
        $output = $this->execute(
            new Input(
                'skill-hybrid',
                'Feature Implementation Skill',
                'Guides feature implementation with templates',
                '## When to Use\nUse when implementing new features.',
                [
                    'type'      => 'hybrid',
                    'steps'     => [
                        ['order' => 1, 'title' => 'Analyze Requirements', 'content' => 'Understand what needs to be built', 'type' => 'action'],
                    ],
                    'templates' => [
                        ['name' => 'Controller', 'content' => '<?php class Controller {}'],
                    ],
                ],
            ),
        );

        self::assertInstanceOf(Output::class, $output);
        $skill = $this->findEntity(Skill::class, ['id' => DirectiveId::fromString('skill-hybrid')], true);

        self::assertEquals($output->skill, $skill);
        $this->assertSkill(
            $skill,
            'Feature Implementation Skill',
            'Guides feature implementation with templates',
            '## When to Use\nUse when implementing new features.',
            HybridWorkflow::class,
            0,
        );

        self::assertDomainEventHasBeenDispatched(DirectiveDrafted::class);
    }

    public function testItShouldDraftSkillWithoutExamples(): void
    {
        $output = $this->execute(
            new Input(
                'skill-no-examples',
                'Minimal Skill',
                'A minimal skill',
                'Content',
                [
                    'type'  => 'sequential',
                    'steps' => [
                        ['order' => 1, 'title' => 'Step 1', 'content' => 'Do something', 'type' => 'action'],
                    ],
                ],
            ),
        );

        self::assertInstanceOf(Output::class, $output);
        $skill = $this->findEntity(Skill::class, ['id' => DirectiveId::fromString('skill-no-examples')], true);

        self::assertEquals($output->skill, $skill);
        self::assertEquals(SkillExamples::empty(), $skill->examples);

        self::assertDomainEventHasBeenDispatched(DirectiveDrafted::class);
    }

    public function testItShouldDraftSkillWithMultipleExamples(): void
    {
        $output = $this->execute(
            new Input(
                'skill-multi-examples',
                'Skill With Examples',
                'Description',
                'Content',
                [
                    'type'  => 'sequential',
                    'steps' => [
                        ['order' => 1, 'title' => 'Step', 'content' => 'Do it', 'type' => 'action'],
                    ],
                ],
                [
                    ['scenario' => 'Scenario 1', 'input' => 'Input 1', 'output' => 'Output 1'],
                    ['scenario' => 'Scenario 2', 'input' => 'Input 2', 'output' => 'Output 2', 'explanation' => 'Why'],
                ],
            ),
        );

        self::assertInstanceOf(Output::class, $output);
        $skill = $this->findEntity(Skill::class, ['id' => DirectiveId::fromString('skill-multi-examples')], true);

        self::assertEquals($output->skill, $skill);
        self::assertCount(2, $skill->examples);

        self::assertDomainEventHasBeenDispatched(DirectiveDrafted::class);
    }

    /**
     * @param class-string<SkillWorkflow> $expectedWorkflowClass
     */
    private function assertSkill(
        Skill $skill,
        string $expectedName,
        string $expectedDescription,
        string $expectedContent,
        string $expectedWorkflowClass,
        int $expectedExamplesCount,
    ): void {
        self::assertEquals(
            DirectiveMetadata::create(
                DirectiveName::fromString($expectedName),
                DirectiveDescription::fromString($expectedDescription),
            ),
            $skill->metadata,
        );

        self::assertEquals(
            SkillContent::fromString($expectedContent),
            $skill->content,
        );

        self::assertInstanceOf($expectedWorkflowClass, $skill->workflow);

        self::assertCount($expectedExamplesCount, $skill->examples);
    }
}
