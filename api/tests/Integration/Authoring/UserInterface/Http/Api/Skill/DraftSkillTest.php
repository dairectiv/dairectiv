<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\Authoring\UserInterface\Http\Api\Skill;

use Cake\Chronos\Chronos;
use Dairectiv\Authoring\Domain\Object\Directive\DirectiveId;
use Dairectiv\Authoring\Domain\Object\Directive\Event\DirectiveDrafted;
use Dairectiv\Authoring\Domain\Object\Directive\Metadata\DirectiveDescription;
use Dairectiv\Authoring\Domain\Object\Directive\Metadata\DirectiveMetadata;
use Dairectiv\Authoring\Domain\Object\Directive\Metadata\DirectiveName;
use Dairectiv\Authoring\Domain\Object\Skill\Skill;
use Dairectiv\Authoring\Domain\Object\Skill\SkillContent;
use Dairectiv\Authoring\Domain\Object\Skill\Workflow\ChecklistWorkflow;
use Dairectiv\Authoring\Domain\Object\Skill\Workflow\HybridWorkflow;
use Dairectiv\Authoring\Domain\Object\Skill\Workflow\SequentialWorkflow;
use Dairectiv\Authoring\Domain\Object\Skill\Workflow\SkillWorkflow;
use Dairectiv\Authoring\Domain\Object\Skill\Workflow\TemplateWorkflow;
use Dairectiv\Tests\Framework\IntegrationTestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('integration')]
#[Group('authoring')]
#[Group('api')]
final class DraftSkillTest extends IntegrationTestCase
{
    public function testItShouldDraftSkill(): void
    {
        $this->postJson('/api/authoring/skills', [
            'id'          => 'skill-sequential',
            'name'        => 'Git Commit Skill',
            'description' => 'Creates structured git commit messages',
            'content'     => '## When to Use\nUse this skill when committing changes.',
            'workflow'    => [
                'type'  => 'sequential',
                'steps' => [
                    ['order' => 1, 'title' => 'Gather Context', 'content' => 'Run git status and git diff', 'type' => 'action'],
                    ['order' => 2, 'title' => 'Analyze Changes', 'content' => 'Determine commit type', 'type' => 'decision'],
                    ['order' => 3, 'title' => 'Create Commit', 'content' => 'Execute git commit', 'type' => 'action'],
                ],
            ],
            'examples' => [
                ['scenario' => 'Feature commit', 'input' => 'New login form', 'output' => 'feat: add login form'],
            ],
        ]);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(201);
        self::assertResponseHeaderSame('content-type', 'application/json');
        self::assertResponseReturnsJson([
            'id'          => 'skill-sequential',
            'state'       => 'draft',
            'createdAt'   => Chronos::now()->toIso8601String(),
            'updatedAt'   => Chronos::now()->toIso8601String(),
            'name'        => 'Git Commit Skill',
            'description' => 'Creates structured git commit messages',
            'content'     => '## When to Use\nUse this skill when committing changes.',
            'workflow'    => [
                'type'  => 'sequential',
                'steps' => [
                    ['order' => 1, 'title' => 'Gather Context', 'content' => 'Run git status and git diff', 'type' => 'action', 'condition' => null],
                    ['order' => 2, 'title' => 'Analyze Changes', 'content' => 'Determine commit type', 'type' => 'decision', 'condition' => null],
                    ['order' => 3, 'title' => 'Create Commit', 'content' => 'Execute git commit', 'type' => 'action', 'condition' => null],
                ],
            ],
            'examples' => [
                ['scenario' => 'Feature commit', 'input' => 'New login form', 'output' => 'feat: add login form', 'explanation' => null],
            ],
        ]);

        $skill = $this->findEntity(Skill::class, ['id' => DirectiveId::fromString('skill-sequential')], true);

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
        $this->postJson('/api/authoring/skills', [
            'id'          => 'skill-template',
            'name'        => 'Aggregate Root Skill',
            'description' => 'Creates DDD aggregate roots',
            'content'     => '## When to Use\nUse when implementing new aggregates.',
            'workflow'    => [
                'type'      => 'template',
                'templates' => [
                    ['name' => 'Entity', 'content' => '<?php class Entity {}'],
                    ['name' => 'Repository', 'content' => '<?php interface Repository {}'],
                ],
            ],
        ]);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(201);
        self::assertResponseHeaderSame('content-type', 'application/json');
        self::assertResponseReturnsJson([
            'id'          => 'skill-template',
            'state'       => 'draft',
            'createdAt'   => Chronos::now()->toIso8601String(),
            'updatedAt'   => Chronos::now()->toIso8601String(),
            'name'        => 'Aggregate Root Skill',
            'description' => 'Creates DDD aggregate roots',
            'content'     => '## When to Use\nUse when implementing new aggregates.',
            'workflow'    => [
                'type'      => 'template',
                'templates' => [
                    ['name' => 'Entity', 'content' => '<?php class Entity {}', 'description' => null],
                    ['name' => 'Repository', 'content' => '<?php interface Repository {}', 'description' => null],
                ],
            ],
            'examples' => [],
        ]);

        $skill = $this->findEntity(Skill::class, ['id' => DirectiveId::fromString('skill-template')], true);

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
        $this->postJson('/api/authoring/skills', [
            'id'          => 'skill-checklist',
            'name'        => 'Code Review Skill',
            'description' => 'Guides code review process',
            'content'     => '## When to Use\nUse when reviewing pull requests.',
            'workflow'    => [
                'type'  => 'checklist',
                'items' => [
                    ['order' => 1, 'title' => 'Check Tests', 'content' => 'Verify all tests pass', 'type' => 'validation'],
                    ['order' => 2, 'title' => 'Review Logic', 'content' => 'Check business logic', 'type' => 'action'],
                ],
            ],
        ]);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(201);
        self::assertResponseHeaderSame('content-type', 'application/json');
        self::assertResponseReturnsJson([
            'id'          => 'skill-checklist',
            'state'       => 'draft',
            'createdAt'   => Chronos::now()->toIso8601String(),
            'updatedAt'   => Chronos::now()->toIso8601String(),
            'name'        => 'Code Review Skill',
            'description' => 'Guides code review process',
            'content'     => '## When to Use\nUse when reviewing pull requests.',
            'workflow'    => [
                'type'  => 'checklist',
                'items' => [
                    ['order' => 1, 'title' => 'Check Tests', 'content' => 'Verify all tests pass', 'type' => 'validation', 'condition' => null],
                    ['order' => 2, 'title' => 'Review Logic', 'content' => 'Check business logic', 'type' => 'action', 'condition' => null],
                ],
            ],
            'examples' => [],
        ]);

        $skill = $this->findEntity(Skill::class, ['id' => DirectiveId::fromString('skill-checklist')], true);

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
        $this->postJson('/api/authoring/skills', [
            'id'          => 'skill-hybrid',
            'name'        => 'Feature Implementation Skill',
            'description' => 'Guides feature implementation with templates',
            'content'     => '## When to Use\nUse when implementing new features.',
            'workflow'    => [
                'type'  => 'hybrid',
                'steps' => [
                    ['order' => 1, 'title' => 'Analyze Requirements', 'content' => 'Understand what needs to be built', 'type' => 'action'],
                ],
                'templates' => [
                    ['name' => 'Controller', 'content' => '<?php class Controller {}'],
                ],
            ],
        ]);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(201);
        self::assertResponseHeaderSame('content-type', 'application/json');
        self::assertResponseReturnsJson([
            'id'          => 'skill-hybrid',
            'state'       => 'draft',
            'createdAt'   => Chronos::now()->toIso8601String(),
            'updatedAt'   => Chronos::now()->toIso8601String(),
            'name'        => 'Feature Implementation Skill',
            'description' => 'Guides feature implementation with templates',
            'content'     => '## When to Use\nUse when implementing new features.',
            'workflow'    => [
                'type'  => 'hybrid',
                'steps' => [
                    ['order' => 1, 'title' => 'Analyze Requirements', 'content' => 'Understand what needs to be built', 'type' => 'action', 'condition' => null],
                ],
                'templates' => [
                    ['name' => 'Controller', 'content' => '<?php class Controller {}', 'description' => null],
                ],
            ],
            'examples' => [],
        ]);

        $skill = $this->findEntity(Skill::class, ['id' => DirectiveId::fromString('skill-hybrid')], true);

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
        $this->postJson('/api/authoring/skills', [
            'id'          => 'skill-no-examples',
            'name'        => 'Minimal Skill',
            'description' => 'A minimal skill',
            'content'     => 'Content',
            'workflow'    => [
                'type'  => 'sequential',
                'steps' => [
                    ['order' => 1, 'title' => 'Step 1', 'content' => 'Do something', 'type' => 'action'],
                ],
            ],
        ]);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(201);
        self::assertResponseHeaderSame('content-type', 'application/json');
        self::assertResponseReturnsJson([
            'id'          => 'skill-no-examples',
            'state'       => 'draft',
            'createdAt'   => Chronos::now()->toIso8601String(),
            'updatedAt'   => Chronos::now()->toIso8601String(),
            'name'        => 'Minimal Skill',
            'description' => 'A minimal skill',
            'content'     => 'Content',
            'workflow'    => [
                'type'  => 'sequential',
                'steps' => [
                    ['order' => 1, 'title' => 'Step 1', 'content' => 'Do something', 'type' => 'action', 'condition' => null],
                ],
            ],
            'examples' => [],
        ]);

        $skill = $this->findEntity(Skill::class, ['id' => DirectiveId::fromString('skill-no-examples')], true);

        $this->assertSkill(
            $skill,
            'Minimal Skill',
            'A minimal skill',
            'Content',
            SequentialWorkflow::class,
            0,
        );

        self::assertDomainEventHasBeenDispatched(DirectiveDrafted::class);
    }

    public function testItShouldDraftSkillWithMultipleExamples(): void
    {
        $this->postJson('/api/authoring/skills', [
            'id'          => 'skill-multi-examples',
            'name'        => 'Skill With Examples',
            'description' => 'Description',
            'content'     => 'Content',
            'workflow'    => [
                'type'  => 'sequential',
                'steps' => [
                    ['order' => 1, 'title' => 'Step', 'content' => 'Do it', 'type' => 'action'],
                ],
            ],
            'examples' => [
                ['scenario' => 'Scenario 1', 'input' => 'Input 1', 'output' => 'Output 1'],
                ['scenario' => 'Scenario 2', 'input' => 'Input 2', 'output' => 'Output 2', 'explanation' => 'Why'],
            ],
        ]);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(201);
        self::assertResponseHeaderSame('content-type', 'application/json');
        self::assertResponseReturnsJson([
            'id'          => 'skill-multi-examples',
            'state'       => 'draft',
            'createdAt'   => Chronos::now()->toIso8601String(),
            'updatedAt'   => Chronos::now()->toIso8601String(),
            'name'        => 'Skill With Examples',
            'description' => 'Description',
            'content'     => 'Content',
            'workflow'    => [
                'type'  => 'sequential',
                'steps' => [
                    ['order' => 1, 'title' => 'Step', 'content' => 'Do it', 'type' => 'action', 'condition' => null],
                ],
            ],
            'examples' => [
                ['scenario' => 'Scenario 1', 'input' => 'Input 1', 'output' => 'Output 1', 'explanation' => null],
                ['scenario' => 'Scenario 2', 'input' => 'Input 2', 'output' => 'Output 2', 'explanation' => 'Why'],
            ],
        ]);

        $skill = $this->findEntity(Skill::class, ['id' => DirectiveId::fromString('skill-multi-examples')], true);

        $this->assertSkill(
            $skill,
            'Skill With Examples',
            'Description',
            'Content',
            SequentialWorkflow::class,
            2,
        );

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
