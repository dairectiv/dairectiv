<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Unit\Authoring\Domain\Object\Skill\Workflow;

use Dairectiv\Authoring\Domain\Object\Skill\Workflow\ChecklistWorkflow;
use Dairectiv\Authoring\Domain\Object\Skill\Workflow\HybridWorkflow;
use Dairectiv\Authoring\Domain\Object\Skill\Workflow\SequentialWorkflow;
use Dairectiv\Authoring\Domain\Object\Skill\Workflow\SkillStep;
use Dairectiv\Authoring\Domain\Object\Skill\Workflow\SkillTemplate;
use Dairectiv\Authoring\Domain\Object\Skill\Workflow\SkillWorkflow;
use Dairectiv\Authoring\Domain\Object\Skill\Workflow\TemplateWorkflow;
use Dairectiv\Authoring\Domain\Object\Skill\Workflow\WorkflowType;
use Dairectiv\SharedKernel\Domain\Object\Exception\InvalidArgumentException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
#[Group('authoring')]
final class SkillWorkflowTest extends TestCase
{
    public function testItShouldCreateSequentialWorkflow(): void
    {
        $steps = [
            SkillStep::action(1, 'Step 1', 'First step'),
            SkillStep::validation(2, 'Step 2', 'Second step'),
        ];

        $workflow = SequentialWorkflow::create($steps);

        self::assertSame(WorkflowType::Sequential, $workflow->getType());
        self::assertSame(2, $workflow->stepCount());
        self::assertSame($steps, $workflow->steps);
    }

    public function testItShouldThrowExceptionForEmptySequentialWorkflow(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Sequential workflow must have at least one step.');

        SequentialWorkflow::create([]);
    }

    public function testItShouldCreateTemplateWorkflow(): void
    {
        $templates = [
            SkillTemplate::create('Entity', '<?php class Entity {}'),
            SkillTemplate::create('Repository', '<?php interface Repository {}'),
        ];

        $workflow = TemplateWorkflow::create($templates);

        self::assertSame(WorkflowType::Template, $workflow->getType());
        self::assertSame(2, $workflow->templateCount());
        self::assertSame($templates, $workflow->templates);
    }

    public function testItShouldThrowExceptionForEmptyTemplateWorkflow(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Template workflow must have at least one template.');

        TemplateWorkflow::create([]);
    }

    public function testItShouldCreateChecklistWorkflow(): void
    {
        $items = [
            SkillStep::action(1, 'Check 1', 'First check'),
            SkillStep::action(2, 'Check 2', 'Second check'),
        ];

        $workflow = ChecklistWorkflow::create($items);

        self::assertSame(WorkflowType::Checklist, $workflow->getType());
        self::assertSame(2, $workflow->itemCount());
        self::assertSame($items, $workflow->items);
    }

    public function testItShouldThrowExceptionForEmptyChecklistWorkflow(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Checklist workflow must have at least one item.');

        ChecklistWorkflow::create([]);
    }

    public function testItShouldCreateHybridWorkflowWithStepsAndTemplates(): void
    {
        $steps = [SkillStep::action(1, 'Step', 'Content')];
        $templates = [SkillTemplate::create('Template', 'Content')];

        $workflow = HybridWorkflow::create($steps, $templates);

        self::assertSame(WorkflowType::Hybrid, $workflow->getType());
        self::assertSame(1, $workflow->stepCount());
        self::assertSame(1, $workflow->templateCount());
    }

    public function testItShouldCreateHybridWorkflowWithOnlySteps(): void
    {
        $steps = [SkillStep::action(1, 'Step', 'Content')];

        $workflow = HybridWorkflow::create($steps, []);

        self::assertSame(1, $workflow->stepCount());
        self::assertSame(0, $workflow->templateCount());
    }

    public function testItShouldCreateHybridWorkflowWithOnlyTemplates(): void
    {
        $templates = [SkillTemplate::create('Template', 'Content')];

        $workflow = HybridWorkflow::create([], $templates);

        self::assertSame(0, $workflow->stepCount());
        self::assertSame(1, $workflow->templateCount());
    }

    public function testItShouldThrowExceptionForEmptyHybridWorkflow(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Hybrid workflow must have at least one step or template.');

        HybridWorkflow::create([], []);
    }

    public function testItShouldConvertSequentialWorkflowToArray(): void
    {
        $workflow = SequentialWorkflow::create([
            SkillStep::action(1, 'Step', 'Content'),
        ]);

        $array = $workflow->toArray();

        self::assertSame('sequential', $array['type']);
        self::assertIsArray($array['steps']);
        self::assertCount(1, $array['steps']);
    }

    public function testItShouldConvertTemplateWorkflowToArray(): void
    {
        $workflow = TemplateWorkflow::create([
            SkillTemplate::create('Template', 'Content'),
        ]);

        $array = $workflow->toArray();

        self::assertSame('template', $array['type']);
        self::assertIsArray($array['templates']);
        self::assertCount(1, $array['templates']);
    }

    public function testItShouldConvertChecklistWorkflowToArray(): void
    {
        $workflow = ChecklistWorkflow::create([
            SkillStep::action(1, 'Item', 'Content'),
        ]);

        $array = $workflow->toArray();

        self::assertSame('checklist', $array['type']);
        self::assertIsArray($array['items']);
        self::assertCount(1, $array['items']);
    }

    public function testItShouldConvertHybridWorkflowToArray(): void
    {
        $workflow = HybridWorkflow::create(
            [SkillStep::action(1, 'Step', 'Content')],
            [SkillTemplate::create('Template', 'Content')],
        );

        $array = $workflow->toArray();

        self::assertSame('hybrid', $array['type']);
        self::assertIsArray($array['steps']);
        self::assertCount(1, $array['steps']);
        self::assertIsArray($array['templates']);
        self::assertCount(1, $array['templates']);
    }

    public function testItShouldCreateSequentialWorkflowFromArray(): void
    {
        $workflow = SkillWorkflow::fromArray([
            'type'  => 'sequential',
            'steps' => [
                ['order' => 1, 'title' => 'Step', 'content' => 'Content', 'type' => 'action'],
            ],
        ]);

        self::assertInstanceOf(SequentialWorkflow::class, $workflow);
        self::assertSame(WorkflowType::Sequential, $workflow->getType());
    }

    public function testItShouldCreateTemplateWorkflowFromArray(): void
    {
        $workflow = SkillWorkflow::fromArray([
            'type'      => 'template',
            'templates' => [
                ['name' => 'Template', 'content' => 'Content'],
            ],
        ]);

        self::assertInstanceOf(TemplateWorkflow::class, $workflow);
        self::assertSame(WorkflowType::Template, $workflow->getType());
    }

    public function testItShouldCreateChecklistWorkflowFromArray(): void
    {
        $workflow = SkillWorkflow::fromArray([
            'type'  => 'checklist',
            'items' => [
                ['order' => 1, 'title' => 'Item', 'content' => 'Content', 'type' => 'action'],
            ],
        ]);

        self::assertInstanceOf(ChecklistWorkflow::class, $workflow);
        self::assertSame(WorkflowType::Checklist, $workflow->getType());
    }

    public function testItShouldCreateHybridWorkflowFromArray(): void
    {
        $workflow = SkillWorkflow::fromArray([
            'type'      => 'hybrid',
            'steps'     => [
                ['order' => 1, 'title' => 'Step', 'content' => 'Content', 'type' => 'action'],
            ],
            'templates' => [
                ['name' => 'Template', 'content' => 'Content'],
            ],
        ]);

        self::assertInstanceOf(HybridWorkflow::class, $workflow);
        self::assertSame(WorkflowType::Hybrid, $workflow->getType());
    }
}
