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
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

#[Group('integration')]
#[Group('authoring')]
#[Group('api')]
final class DraftSkillTest extends IntegrationTestCase
{
    /**
     * @return iterable<string, array{payload: array<array-key, mixed>, expectedViolations: array<array{propertyPath: string, title: string}>}>
     */
    public static function provideInvalidPayloads(): iterable
    {
        yield 'id not in kebab case' => [
            'payload'            => self::createPayload(['id' => 'Non Kebab Case']),
            'expectedViolations' => [
                [
                    'propertyPath' => 'id',
                    'title'        => 'This value is not valid.',
                ],
            ],
        ];
        yield 'blank id' => [
            'payload'            => self::createPayload(['id' => '']),
            'expectedViolations' => [
                [
                    'propertyPath' => 'id',
                    'title'        => 'This value should not be blank.',
                ],
            ],
        ];
        yield 'blank name' => [
            'payload'            => self::createPayload(['name' => '']),
            'expectedViolations' => [
                [
                    'propertyPath' => 'name',
                    'title'        => 'This value should not be blank.',
                ],
            ],
        ];
        yield 'too long name' => [
            'payload'            => self::createPayload(['name' => self::faker()->realTextBetween(256, 500)]),
            'expectedViolations' => [
                [
                    'propertyPath' => 'name',
                    'title'        => 'This value is too long. It should have 255 characters or less.',
                ],
            ],
        ];
        yield 'blank description' => [
            'payload'            => self::createPayload(['description' => '']),
            'expectedViolations' => [
                [
                    'propertyPath' => 'description',
                    'title'        => 'This value should not be blank.',
                ],
            ],
        ];
        yield 'too long description' => [
            'payload'            => self::createPayload(['description' => self::faker()->realTextBetween(501, 1000)]),
            'expectedViolations' => [
                [
                    'propertyPath' => 'description',
                    'title'        => 'This value is too long. It should have 500 characters or less.',
                ],
            ],
        ];
        yield 'blank content' => [
            'payload'            => self::createPayload(['content' => '']),
            'expectedViolations' => [
                [
                    'propertyPath' => 'content',
                    'title'        => 'This value should not be blank.',
                ],
            ],
        ];
        yield 'blank example scenario' => [
            'payload'            => self::createExamples([['scenario' => '', 'input' => 'Input', 'output' => 'Output']]),
            'expectedViolations' => [
                [
                    'propertyPath' => 'examples[0].scenario',
                    'title'        => 'This value should not be blank.',
                ],
            ],
        ];
        yield 'blank example input' => [
            'payload'            => self::createExamples([['scenario' => 'Scenario', 'input' => '', 'output' => 'Output']]),
            'expectedViolations' => [
                [
                    'propertyPath' => 'examples[0].input',
                    'title'        => 'This value should not be blank.',
                ],
            ],
        ];
        yield 'blank example output' => [
            'payload'            => self::createExamples([['scenario' => 'Scenario', 'input' => 'Input', 'output' => '']]),
            'expectedViolations' => [
                [
                    'propertyPath' => 'examples[0].output',
                    'title'        => 'This value should not be blank.',
                ],
            ],
        ];
        yield 'blank example explanation' => [
            'payload'            => self::createExamples([['scenario' => 'Scenario', 'input' => 'Input', 'output' => 'Output', 'explanation' => '']]),
            'expectedViolations' => [
                [
                    'propertyPath' => 'examples[0].explanation',
                    'title'        => 'This value should not be blank.',
                ],
            ],
        ];

        // Workflow type
        yield 'invalid workflow type' => [
            'payload'            => self::createPayload(['workflow' => ['type' => 'invalid']]),
            'expectedViolations' => [
                [
                    'propertyPath' => 'workflow.type',
                    'title'        => 'This value should be of type string.',
                ],
            ],
        ];

        // Sequential workflow
        yield 'sequential workflow empty steps' => [
            'payload'            => self::createSequentialWorkflowPayload([]),
            'expectedViolations' => [
                [
                    'propertyPath' => 'workflow.steps',
                    'title'        => 'The steps order is invalid.',
                ],
                [
                    'propertyPath' => 'workflow.steps',
                    'title'        => 'This collection should contain 1 element or more.',
                ],
            ],
        ];
        yield 'sequential workflow invalid steps order' => [
            'payload'            => self::createSequentialWorkflowPayload([
                ['order' => 1, 'title' => 'Step 1', 'content' => 'Content', 'type' => 'action'],
                ['order' => 3, 'title' => 'Step 3', 'content' => 'Content', 'type' => 'action'],
            ]),
            'expectedViolations' => [
                [
                    'propertyPath' => 'workflow.steps',
                    'title'        => 'The steps order is invalid.',
                ],
            ],
        ];
        yield 'sequential workflow step negative order' => [
            'payload'            => self::createSequentialWorkflowPayload([
                ['order' => -1, 'title' => 'Step', 'content' => 'Content', 'type' => 'action'],
            ]),
            'expectedViolations' => [
                [
                    'propertyPath' => 'workflow.steps',
                    'title'        => 'The steps order is invalid.',
                ],
                [
                    'propertyPath' => 'workflow.steps[0].order',
                    'title'        => 'This value should be positive.',
                ],
            ],
        ];
        yield 'sequential workflow step zero order' => [
            'payload'            => self::createSequentialWorkflowPayload([
                ['order' => 0, 'title' => 'Step', 'content' => 'Content', 'type' => 'action'],
            ]),
            'expectedViolations' => [
                [
                    'propertyPath' => 'workflow.steps',
                    'title'        => 'The steps order is invalid.',
                ],
                [
                    'propertyPath' => 'workflow.steps[0].order',
                    'title'        => 'This value should be positive.',
                ],
            ],
        ];
        yield 'sequential workflow step blank title' => [
            'payload'            => self::createSequentialWorkflowPayload([
                ['order' => 1, 'title' => '', 'content' => 'Content', 'type' => 'action'],
            ]),
            'expectedViolations' => [
                [
                    'propertyPath' => 'workflow.steps[0].title',
                    'title'        => 'This value should not be blank.',
                ],
            ],
        ];
        yield 'sequential workflow step blank content' => [
            'payload'            => self::createSequentialWorkflowPayload([
                ['order' => 1, 'title' => 'Step', 'content' => '', 'type' => 'action'],
            ]),
            'expectedViolations' => [
                [
                    'propertyPath' => 'workflow.steps[0].content',
                    'title'        => 'This value should not be blank.',
                ],
            ],
        ];
        yield 'sequential workflow step invalid type' => [
            'payload'            => self::createSequentialWorkflowPayload([
                ['order' => 1, 'title' => 'Step', 'content' => 'Content', 'type' => 'invalid'],
            ]),
            'expectedViolations' => [
                [
                    'propertyPath' => 'workflow.steps[0].type',
                    'title'        => 'The value you selected is not a valid choice.',
                ],
            ],
        ];
        yield 'sequential workflow step blank condition' => [
            'payload'            => self::createSequentialWorkflowPayload([
                ['order' => 1, 'title' => 'Step', 'content' => 'Content', 'type' => 'action', 'condition' => ''],
            ]),
            'expectedViolations' => [
                [
                    'propertyPath' => 'workflow.steps[0].condition',
                    'title'        => 'This value should not be blank.',
                ],
            ],
        ];

        // Checklist workflow
        yield 'checklist workflow empty items' => [
            'payload'            => self::createChecklistWorkflowPayload([]),
            'expectedViolations' => [
                [
                    'propertyPath' => 'workflow.items',
                    'title'        => 'This collection should contain 1 element or more.',
                ],
            ],
        ];
        yield 'checklist workflow item blank title' => [
            'payload'            => self::createChecklistWorkflowPayload([
                ['order' => 1, 'title' => '', 'content' => 'Content', 'type' => 'action'],
            ]),
            'expectedViolations' => [
                [
                    'propertyPath' => 'workflow.items[0].title',
                    'title'        => 'This value should not be blank.',
                ],
            ],
        ];

        // Template workflow
        yield 'template workflow empty templates' => [
            'payload'            => self::createTemplateWorkflowPayload([]),
            'expectedViolations' => [
                [
                    'propertyPath' => 'workflow.templates',
                    'title'        => 'This collection should contain 1 element or more.',
                ],
            ],
        ];
        yield 'template workflow template blank name' => [
            'payload'            => self::createTemplateWorkflowPayload([
                ['name' => '', 'content' => 'Content'],
            ]),
            'expectedViolations' => [
                [
                    'propertyPath' => 'workflow.templates[0].name',
                    'title'        => 'This value should not be blank.',
                ],
            ],
        ];
        yield 'template workflow template blank content' => [
            'payload'            => self::createTemplateWorkflowPayload([
                ['name' => 'Template', 'content' => ''],
            ]),
            'expectedViolations' => [
                [
                    'propertyPath' => 'workflow.templates[0].content',
                    'title'        => 'This value should not be blank.',
                ],
            ],
        ];
        yield 'template workflow template blank description' => [
            'payload'            => self::createTemplateWorkflowPayload([
                ['name' => 'Template', 'content' => 'Content', 'description' => ''],
            ]),
            'expectedViolations' => [
                [
                    'propertyPath' => 'workflow.templates[0].description',
                    'title'        => 'This value should not be blank.',
                ],
            ],
        ];

        // Hybrid workflow
        yield 'hybrid workflow empty steps' => [
            'payload'            => self::createHybridWorkflowPayload([], [['name' => 'Template', 'content' => 'Content']]),
            'expectedViolations' => [
                [
                    'propertyPath' => 'workflow.steps',
                    'title'        => 'The steps order is invalid.',
                ],
                [
                    'propertyPath' => 'workflow.steps',
                    'title'        => 'This collection should contain 1 element or more.',
                ],
            ],
        ];
        yield 'hybrid workflow empty templates' => [
            'payload'            => self::createHybridWorkflowPayload([['order' => 1, 'title' => 'Step', 'content' => 'Content', 'type' => 'action']], []),
            'expectedViolations' => [
                [
                    'propertyPath' => 'workflow.templates',
                    'title'        => 'This collection should contain 1 element or more.',
                ],
            ],
        ];
        yield 'hybrid workflow invalid steps order' => [
            'payload'            => self::createHybridWorkflowPayload(
                [
                    ['order' => 1, 'title' => 'Step 1', 'content' => 'Content', 'type' => 'action'],
                    ['order' => 3, 'title' => 'Step 3', 'content' => 'Content', 'type' => 'action'],
                ],
                [['name' => 'Template', 'content' => 'Content']],
            ),
            'expectedViolations' => [
                [
                    'propertyPath' => 'workflow.steps',
                    'title'        => 'The steps order is invalid.',
                ],
            ],
        ];
    }

    /**
     * @param array<array-key, mixed> $payload
     * @param array<array{propertyPath: string, title: string}> $expectedViolations
     */
    #[DataProvider('provideInvalidPayloads')]
    public function testItShouldNotDraftSkillDueToUnprocessableEntity(array $payload, array $expectedViolations): void
    {
        $this->postJson('/api/authoring/skills', $payload);

        self::assertResponseIsUnprocessable();
        self::assertResponseHeaderSame('content-type', 'application/json');
        self::assertResponseReturnsUnprocessableEntity($expectedViolations);
    }

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

    /**
     * @param array<array<array-key, mixed>> $items
     * @return array<array-key, mixed>
     */
    private static function createChecklistWorkflowPayload(array $items): array
    {
        return self::createPayload([
            'workflow' => [
                'type'  => 'checklist',
                'items' => $items,
            ],
        ]);
    }

    /**
     * @param array<array<array-key, mixed>> $steps
     * @param array<array<array-key, mixed>> $templates
     * @return array<array-key, mixed>
     */
    private static function createHybridWorkflowPayload(array $steps, array $templates): array
    {
        return self::createPayload([
            'workflow' => [
                'type'      => 'hybrid',
                'steps'     => $steps,
                'templates' => $templates,
            ],
        ]);
    }

    /**
     * @param array<array<array-key, mixed>> $steps
     * @return array<array-key, mixed>
     */
    private static function createSequentialWorkflowPayload(array $steps): array
    {
        return self::createPayload([
            'workflow' => [
                'type'  => 'sequential',
                'steps' => $steps,
            ],
        ]);
    }

    /**
     * @param array<array<array-key, mixed>> $templates
     * @return array<array-key, mixed>
     */
    private static function createTemplateWorkflowPayload(array $templates): array
    {
        return self::createPayload([
            'workflow' => [
                'type'      => 'template',
                'templates' => $templates,
            ],
        ]);
    }

    /**
     * @param array<array<array-key, mixed>> $examples
     * @return array<array-key, mixed>
     */
    private static function createExamples(array $examples): array
    {
        return self::createPayload([
            'examples' => self::override(
                [['scenario' => 'Scenario', 'input' => 'Input', 'output' => 'Output']],
                $examples,
            ),
        ]);
    }

    /**
     * @param array<array-key, mixed> $payload
     * @return array<array-key, mixed>
     */
    private static function createPayload(array $payload = []): array
    {
        $basePayload = [
            'id'          => 'skill-test',
            'name'        => 'Skill Name',
            'description' => 'Skill Description',
            'content'     => 'Skill Content',
        ];

        if (!isset($payload['examples'])) {
            $basePayload['examples'] = [['scenario' => 'Scenario', 'input' => 'Input', 'output' => 'Output']];
        }

        if (!isset($payload['workflow'])) {
            $basePayload['workflow'] = [
                'type'  => 'sequential',
                'steps' => [
                    ['order' => 1, 'title' => 'Step 1', 'content' => 'Do something', 'type' => 'action'],
                ],
            ];
        }

        return self::override($basePayload, $payload);
    }
}
