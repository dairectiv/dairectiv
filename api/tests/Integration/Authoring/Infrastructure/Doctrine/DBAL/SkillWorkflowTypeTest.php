<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\Authoring\Infrastructure\Doctrine\DBAL;

use Dairectiv\Authoring\Domain\Object\Skill\Workflow\ChecklistWorkflow;
use Dairectiv\Authoring\Domain\Object\Skill\Workflow\HybridWorkflow;
use Dairectiv\Authoring\Domain\Object\Skill\Workflow\SequentialWorkflow;
use Dairectiv\Authoring\Domain\Object\Skill\Workflow\SkillStep;
use Dairectiv\Authoring\Domain\Object\Skill\Workflow\SkillTemplate;
use Dairectiv\Authoring\Domain\Object\Skill\Workflow\SkillWorkflow;
use Dairectiv\Authoring\Domain\Object\Skill\Workflow\TemplateWorkflow;
use Dairectiv\Tests\Framework\IntegrationTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

#[Group('integration')]
#[Group('authoring')]
#[Group('doctrine-type')]
final class SkillWorkflowTypeTest extends IntegrationTestCase
{
    private const string TYPE = 'object_value';

    /**
     * @return iterable<string, array{phpValue: ?SkillWorkflow, databaseValue: ?string}>
     */
    public static function provideValidValues(): iterable
    {
        yield 'nullable value' => [
            'phpValue'      => null,
            'databaseValue' => null,
        ];
        yield 'sequential workflow' => [
            'phpValue'      => SequentialWorkflow::create([
                SkillStep::action(1, 'Step 1', 'First step'),
            ]),
            'databaseValue' => json_encode([
                'class' => SequentialWorkflow::class,
                'state' => [
                    'type'  => 'sequential',
                    'steps' => [
                        ['order' => 1, 'title' => 'Step 1', 'content' => 'First step', 'type' => 'action', 'condition' => null],
                    ],
                ],
            ], \JSON_THROW_ON_ERROR),
        ];
        yield 'template workflow' => [
            'phpValue'      => TemplateWorkflow::create([
                SkillTemplate::create('Entity', '<?php class Entity {}'),
            ]),
            'databaseValue' => json_encode([
                'class' => TemplateWorkflow::class,
                'state' => [
                    'type'      => 'template',
                    'templates' => [
                        ['name' => 'Entity', 'content' => '<?php class Entity {}', 'description' => null],
                    ],
                ],
            ], \JSON_THROW_ON_ERROR),
        ];
        yield 'checklist workflow' => [
            'phpValue'      => ChecklistWorkflow::create([
                SkillStep::validation(1, 'Check 1', 'First check'),
            ]),
            'databaseValue' => json_encode([
                'class' => ChecklistWorkflow::class,
                'state' => [
                    'type'  => 'checklist',
                    'items' => [
                        ['order' => 1, 'title' => 'Check 1', 'content' => 'First check', 'type' => 'validation', 'condition' => null],
                    ],
                ],
            ], \JSON_THROW_ON_ERROR),
        ];
        yield 'hybrid workflow with steps and templates' => [
            'phpValue'      => HybridWorkflow::create(
                [SkillStep::action(1, 'Step', 'Content')],
                [SkillTemplate::create('Template', 'Code')],
            ),
            'databaseValue' => json_encode([
                'class' => HybridWorkflow::class,
                'state' => [
                    'type'      => 'hybrid',
                    'steps'     => [
                        ['order' => 1, 'title' => 'Step', 'content' => 'Content', 'type' => 'action', 'condition' => null],
                    ],
                    'templates' => [
                        ['name' => 'Template', 'content' => 'Code', 'description' => null],
                    ],
                ],
            ], \JSON_THROW_ON_ERROR),
        ];
    }

    #[DataProvider('provideValidValues')]
    public function testItShouldConvertValueInBothWays(?SkillWorkflow $phpValue, ?string $databaseValue): void
    {
        self::assertConvertToDatabaseValue(
            $databaseValue,
            $phpValue,
            self::TYPE,
        );
        self::assertConvertToPhpValue(
            $phpValue,
            $databaseValue,
            self::TYPE,
        );
    }
}
