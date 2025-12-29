<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Unit\Authoring\Domain\Object\Skill\Workflow;

use Dairectiv\Authoring\Domain\Object\Skill\Workflow\SkillStep;
use Dairectiv\Authoring\Domain\Object\Skill\Workflow\StepType;
use Dairectiv\SharedKernel\Domain\Object\Exception\InvalidArgumentException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
#[Group('authoring')]
final class SkillStepTest extends TestCase
{
    public function testItShouldCreateActionStep(): void
    {
        $step = SkillStep::action(1, 'Run tests', 'Execute the test suite');

        self::assertSame(1, $step->order);
        self::assertSame('Run tests', $step->title);
        self::assertSame('Execute the test suite', $step->content);
        self::assertSame(StepType::Action, $step->type);
        self::assertNull($step->condition);
        self::assertFalse($step->isConditional());
    }

    public function testItShouldCreateDecisionStep(): void
    {
        $step = SkillStep::decision(2, 'Choose approach', 'Select the best implementation');

        self::assertSame(StepType::Decision, $step->type);
    }

    public function testItShouldCreateTemplateStep(): void
    {
        $step = SkillStep::template(3, 'Use pattern', 'Apply the factory pattern');

        self::assertSame(StepType::Template, $step->type);
    }

    public function testItShouldCreateValidationStep(): void
    {
        $step = SkillStep::validation(4, 'Verify result', 'Check the output is correct');

        self::assertSame(StepType::Validation, $step->type);
    }

    public function testItShouldCreateStepWithCondition(): void
    {
        $step = SkillStep::action(1, 'Optional step', 'Only if needed', 'when tests fail');

        self::assertSame('when tests fail', $step->condition);
        self::assertTrue($step->isConditional());
    }

    public function testItShouldThrowExceptionWhenOrderIsNotPositive(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Step order must be a positive integer.');

        SkillStep::action(0, 'Title', 'Content');
    }

    public function testItShouldThrowExceptionWhenTitleIsEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Step title cannot be empty.');

        SkillStep::action(1, '', 'Content');
    }

    public function testItShouldThrowExceptionWhenContentIsEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Step content cannot be empty.');

        SkillStep::action(1, 'Title', '');
    }

    public function testItShouldConvertToArray(): void
    {
        $step = SkillStep::action(1, 'Title', 'Content', 'when needed');

        $array = $step->toArray();

        self::assertSame([
            'order'     => 1,
            'title'     => 'Title',
            'content'   => 'Content',
            'type'      => 'action',
            'condition' => 'when needed',
        ], $array);
    }

    public function testItShouldCreateFromArray(): void
    {
        $step = SkillStep::fromArray([
            'order'     => 2,
            'title'     => 'Step Title',
            'content'   => 'Step Content',
            'type'      => 'decision',
            'condition' => 'if applicable',
        ]);

        self::assertSame(2, $step->order);
        self::assertSame('Step Title', $step->title);
        self::assertSame('Step Content', $step->content);
        self::assertSame(StepType::Decision, $step->type);
        self::assertSame('if applicable', $step->condition);
    }

    public function testItShouldCreateFromArrayWithoutCondition(): void
    {
        $step = SkillStep::fromArray([
            'order'   => 1,
            'title'   => 'Title',
            'content' => 'Content',
            'type'    => 'action',
        ]);

        self::assertNull($step->condition);
    }
}
