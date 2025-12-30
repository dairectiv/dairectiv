<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Unit\Authoring\Domain\Object\Skill;

use Cake\Chronos\Chronos;
use Dairectiv\Authoring\Domain\Object\Directive\DirectiveId;
use Dairectiv\Authoring\Domain\Object\Directive\DirectiveState;
use Dairectiv\Authoring\Domain\Object\Directive\Event\DirectiveArchived;
use Dairectiv\Authoring\Domain\Object\Directive\Event\DirectiveDrafted;
use Dairectiv\Authoring\Domain\Object\Directive\Event\DirectivePublished;
use Dairectiv\Authoring\Domain\Object\Directive\Event\DirectiveUpdated;
use Dairectiv\Authoring\Domain\Object\Skill\Example\Example;
use Dairectiv\Authoring\Domain\Object\Skill\Example\ExampleId;
use Dairectiv\Authoring\Domain\Object\Skill\Skill;
use Dairectiv\Authoring\Domain\Object\Skill\Workflow\Step;
use Dairectiv\Authoring\Domain\Object\Skill\Workflow\StepId;
use Dairectiv\SharedKernel\Domain\Object\Exception\InvalidArgumentException;
use Dairectiv\Tests\Framework\UnitTestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('authoring')]
#[Group('unit')]
final class SkillTest extends UnitTestCase
{
    // ========================================================================
    // SKILL LIFECYCLE
    // ========================================================================

    public function testItShouldDraftSkill(): void
    {
        $id = DirectiveId::fromString('my-skill');

        $skill = Skill::draft($id, 'My Skill', 'A description of my skill');

        self::assertSame($id, $skill->id);
        self::assertSame('my-skill', (string) $skill->id);
        self::assertSame('My Skill', $skill->name);
        self::assertSame('A description of my skill', $skill->description);
        self::assertSame(DirectiveState::Draft, $skill->state);
        self::assertTrue($skill->examples->isEmpty());
        self::assertTrue($skill->steps->isEmpty());
        self::assertEquals(Chronos::now(), $skill->createdAt);
        self::assertEquals(Chronos::now(), $skill->updatedAt);

        $this->assertDomainEventRecorded(DirectiveDrafted::class);
    }

    public function testItShouldUpdateMetadata(): void
    {
        $skill = Skill::draft(DirectiveId::fromString('my-skill'), 'Original Name', 'Original Description');
        $this->resetDomainEvents();

        Chronos::setTestNow(Chronos::now()->addMinutes(1));
        $skill->updateMetadata('Updated Name', 'Updated Description');

        self::assertSame('Updated Name', $skill->name);
        self::assertSame('Updated Description', $skill->description);
        self::assertEquals(Chronos::now(), $skill->updatedAt);

        $this->assertDomainEventRecorded(DirectiveUpdated::class);
    }

    public function testItShouldPublishSkill(): void
    {
        $skill = Skill::draft(DirectiveId::fromString('my-skill'), 'My Skill', 'Description');
        $this->resetDomainEvents();

        Chronos::setTestNow(Chronos::now()->addMinutes(1));
        $skill->publish();

        self::assertSame(DirectiveState::Published, $skill->state);
        self::assertEquals(Chronos::now(), $skill->updatedAt);

        $this->assertDomainEventRecorded(DirectivePublished::class);
    }

    public function testItShouldArchiveSkill(): void
    {
        $skill = Skill::draft(DirectiveId::fromString('my-skill'), 'My Skill', 'Description');
        $skill->publish();
        $this->resetDomainEvents();

        Chronos::setTestNow(Chronos::now()->addMinutes(1));
        $skill->archive();

        self::assertSame(DirectiveState::Archived, $skill->state);
        self::assertEquals(Chronos::now(), $skill->updatedAt);

        $this->assertDomainEventRecorded(DirectiveArchived::class);
    }

    public function testItShouldUpdateContent(): void
    {
        $skill = Skill::draft(DirectiveId::fromString('my-skill'), 'My Skill', 'Description');
        $this->resetDomainEvents();

        Chronos::setTestNow(Chronos::now()->addMinutes(1));
        $skill->updateContent('New content for the skill');

        self::assertSame('New content for the skill', $skill->content);
        self::assertEquals(Chronos::now(), $skill->updatedAt);

        $this->assertDomainEventRecorded(DirectiveUpdated::class);
    }

    // ========================================================================
    // SKILL LIFECYCLE - INVALID STATE TRANSITIONS
    // ========================================================================

    public function testItShouldNotPublishAlreadyPublishedSkill(): void
    {
        $skill = Skill::draft(DirectiveId::fromString('my-skill'), 'My Skill', 'Description');
        $skill->publish();
        $this->resetDomainEvents();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Only draft directives can be published.');

        $skill->publish();
    }

    public function testItShouldNotPublishArchivedSkill(): void
    {
        $skill = Skill::draft(DirectiveId::fromString('my-skill'), 'My Skill', 'Description');
        $skill->archive();
        $this->resetDomainEvents();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Only draft directives can be published.');

        $skill->publish();
    }

    public function testItShouldNotArchiveAlreadyArchivedSkill(): void
    {
        $skill = Skill::draft(DirectiveId::fromString('my-skill'), 'My Skill', 'Description');
        $skill->archive();
        $this->resetDomainEvents();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Directive is already archived.');

        $skill->archive();
    }

    public function testItShouldNotUpdateMetadataOfArchivedSkill(): void
    {
        $skill = Skill::draft(DirectiveId::fromString('my-skill'), 'My Skill', 'Description');
        $skill->archive();
        $this->resetDomainEvents();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot perform this action on an archived directive.');

        $skill->updateMetadata('New Name', 'New Description');
    }

    public function testItShouldNotUpdateMetadataWithNullFields(): void
    {
        $skill = Skill::draft(DirectiveId::fromString('my-skill'), 'My Skill', 'Description');
        $this->resetDomainEvents();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('At least one metadata field must be provided.');

        $skill->updateMetadata(null, null);
    }

    public function testItShouldNotUpdateContentOfArchivedSkill(): void
    {
        $skill = Skill::draft(DirectiveId::fromString('my-skill'), 'My Skill', 'Description');
        $skill->archive();
        $this->resetDomainEvents();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot perform this action on an archived directive.');

        $skill->updateContent('New content');
    }

    public function testItShouldNotAddExampleToArchivedSkill(): void
    {
        $skill = Skill::draft(DirectiveId::fromString('my-skill'), 'My Skill', 'Description');
        $skill->archive();
        $this->resetDomainEvents();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot perform this action on an archived directive.');

        Example::create($skill, 'Scenario', 'Input', 'Output', 'Explanation');
    }

    public function testItShouldNotAddStepToArchivedSkill(): void
    {
        $skill = Skill::draft(DirectiveId::fromString('my-skill'), 'My Skill', 'Description');
        $skill->archive();
        $this->resetDomainEvents();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot perform this action on an archived directive.');

        Step::create($skill, 'Step content');
    }

    // ========================================================================
    // EXAMPLE MANAGEMENT
    // ========================================================================

    public function testItShouldAddExample(): void
    {
        $skill = Skill::draft(DirectiveId::fromString('my-skill'), 'My Skill', 'Description');
        $this->resetDomainEvents();

        Chronos::setTestNow(Chronos::now()->addMinutes(1));

        $exampleId = ExampleId::setNext();

        $example = Example::create($skill, 'Scenario', 'Input', 'Output', 'Explanation');

        self::assertTrue($exampleId->equals($example->id));
        self::assertSame('Scenario', $example->scenario);
        self::assertSame('Input', $example->input);
        self::assertSame('Output', $example->output);
        self::assertSame('Explanation', $example->explanation);
        self::assertSame($skill, $example->skill);
        self::assertEquals(Chronos::now(), $example->createdAt);
        self::assertEquals(Chronos::now(), $example->updatedAt);

        self::assertEquals(Chronos::now(), $skill->updatedAt);
        self::assertTrue($skill->examples->contains($example));

        $this->assertDomainEventRecorded(DirectiveUpdated::class);
    }

    public function testItShouldAddExampleWithoutExplanation(): void
    {
        $skill = Skill::draft(DirectiveId::fromString('my-skill'), 'My Skill', 'Description');
        $this->resetDomainEvents();

        Chronos::setTestNow(Chronos::now()->addMinutes(1));

        $example = Example::create($skill, 'Scenario', 'Input', 'Output');

        self::assertSame('Scenario', $example->scenario);
        self::assertSame('Input', $example->input);
        self::assertSame('Output', $example->output);
        self::assertNull($example->explanation);
        self::assertSame($skill, $example->skill);

        self::assertTrue($skill->examples->contains($example));

        $this->assertDomainEventRecorded(DirectiveUpdated::class);
    }

    public function testItShouldAddMultipleExamples(): void
    {
        $skill = Skill::draft(DirectiveId::fromString('my-skill'), 'My Skill', 'Description');
        $this->resetDomainEvents();

        $example1 = Example::create($skill, 'Scenario 1', 'Input 1', 'Output 1');
        $example2 = Example::create($skill, 'Scenario 2', 'Input 2', 'Output 2');
        $example3 = Example::create($skill, 'Scenario 3', 'Input 3', 'Output 3');

        self::assertCount(3, $skill->examples);
        self::assertTrue($skill->examples->contains($example1));
        self::assertTrue($skill->examples->contains($example2));
        self::assertTrue($skill->examples->contains($example3));

        $this->assertDomainEventRecorded(DirectiveUpdated::class, 3);
    }

    // ========================================================================
    // STEP MANAGEMENT - CREATION
    // ========================================================================

    public function testItShouldCreateFirstStep(): void
    {
        $skill = Skill::draft(DirectiveId::fromString('my-skill'), 'My Skill', 'Description');
        $this->resetDomainEvents();

        Chronos::setTestNow(Chronos::now()->addMinutes(1));

        $stepId = StepId::setNext();

        // First step (null on empty skill means position 1)
        $step = Step::create($skill, 'First step content');

        self::assertTrue($stepId->equals($step->id));
        self::assertSame(1, $step->order);
        self::assertSame('First step content', $step->content);
        self::assertSame($skill, $step->skill);
        self::assertEquals(Chronos::now(), $step->createdAt);
        self::assertEquals(Chronos::now(), $step->updatedAt);

        self::assertEquals(Chronos::now(), $skill->updatedAt);
        self::assertTrue($skill->steps->contains($step));

        $this->assertDomainEventRecorded(DirectiveUpdated::class);
    }

    public function testItShouldCreateStepAtEnd(): void
    {
        $skill = Skill::draft(DirectiveId::fromString('my-skill'), 'My Skill', 'Description');
        $step1 = Step::create($skill, 'First step');
        $this->resetDomainEvents();

        // Create at the end by passing the last step
        $step2 = Step::create($skill, 'Second step', $step1);

        self::assertSame(1, $step1->order);
        self::assertSame(2, $step2->order);

        $this->assertDomainEventRecorded(DirectiveUpdated::class);
    }

    public function testItShouldCreateStepAtBeginning(): void
    {
        $skill = Skill::draft(DirectiveId::fromString('my-skill'), 'My Skill', 'Description');
        $step1 = Step::create($skill, 'First step');
        $step2 = Step::create($skill, 'Second step', $step1);
        $this->resetDomainEvents();

        // Create at the beginning (null)
        $step3 = Step::create($skill, 'New first step', null);

        self::assertSame(1, $step3->order);
        self::assertSame(2, $step1->order);
        self::assertSame(3, $step2->order);

        $this->assertDomainEventRecorded(DirectiveUpdated::class);
    }

    public function testItShouldCreateStepInMiddle(): void
    {
        $skill = Skill::draft(DirectiveId::fromString('my-skill'), 'My Skill', 'Description');
        $step1 = Step::create($skill, 'Step 1');
        $step2 = Step::create($skill, 'Step 2', $step1);
        $step3 = Step::create($skill, 'Step 3', $step2);
        $this->resetDomainEvents();

        // Insert after step1 (in the middle)
        $step4 = Step::create($skill, 'Step 1.5', $step1);

        self::assertSame(1, $step1->order);
        self::assertSame(2, $step4->order);
        self::assertSame(3, $step2->order);
        self::assertSame(4, $step3->order);

        $this->assertDomainEventRecorded(DirectiveUpdated::class);
    }

    public function testItShouldCreateMultipleStepsSequentially(): void
    {
        $skill = Skill::draft(DirectiveId::fromString('my-skill'), 'My Skill', 'Description');
        $this->resetDomainEvents();

        $step1 = Step::create($skill, 'First step');
        $step2 = Step::create($skill, 'Second step', $step1);
        $step3 = Step::create($skill, 'Third step', $step2);

        self::assertCount(3, $skill->steps);
        self::assertSame(1, $step1->order);
        self::assertSame(2, $step2->order);
        self::assertSame(3, $step3->order);

        $this->assertDomainEventRecorded(DirectiveUpdated::class, 3);
    }

    // ========================================================================
    // STEP MANAGEMENT - UPDATE
    // ========================================================================

    public function testItShouldUpdateStepContent(): void
    {
        $skill = Skill::draft(DirectiveId::fromString('my-skill'), 'My Skill', 'Description');
        $step = Step::create($skill, 'Original content');
        $this->resetDomainEvents();

        Chronos::setTestNow(Chronos::now()->addMinutes(1));

        $step->update('Updated content');

        self::assertSame('Updated content', $step->content);
        self::assertEquals(Chronos::now(), $step->updatedAt);
        self::assertEquals(Chronos::now(), $skill->updatedAt);

        $this->assertDomainEventRecorded(DirectiveUpdated::class);
    }

    // ========================================================================
    // STEP MANAGEMENT - MOVE
    // ========================================================================

    public function testItShouldMoveStepAfterAnother(): void
    {
        $skill = Skill::draft(DirectiveId::fromString('my-skill'), 'My Skill', 'Description');
        $step1 = Step::create($skill, 'Step 1');
        $step2 = Step::create($skill, 'Step 2', $step1);
        $step3 = Step::create($skill, 'Step 3', $step2);
        $step4 = Step::create($skill, 'Step 4', $step3);
        $this->resetDomainEvents();

        // Move step1 after step3
        // Before: 1, 2, 3, 4
        // After:  2, 3, 1, 4
        $skill->moveStepAfter($step1, $step3);

        self::assertSame(3, $step1->order);
        self::assertSame(1, $step2->order);
        self::assertSame(2, $step3->order);
        self::assertSame(4, $step4->order);

        $this->assertDomainEventRecorded(DirectiveUpdated::class);
    }

    public function testItShouldMoveStepToFirstPosition(): void
    {
        $skill = Skill::draft(DirectiveId::fromString('my-skill'), 'My Skill', 'Description');
        $step1 = Step::create($skill, 'Step 1');
        $step2 = Step::create($skill, 'Step 2', $step1);
        $step3 = Step::create($skill, 'Step 3', $step2);
        $this->resetDomainEvents();

        // Move step3 to first position (after null)
        $skill->moveStepAfter($step3, null);

        self::assertSame(1, $step3->order);
        self::assertSame(2, $step1->order);
        self::assertSame(3, $step2->order);

        $this->assertDomainEventRecorded(DirectiveUpdated::class);
    }

    public function testItShouldMoveStepBackward(): void
    {
        $skill = Skill::draft(DirectiveId::fromString('my-skill'), 'My Skill', 'Description');
        $step1 = Step::create($skill, 'Step 1');
        $step2 = Step::create($skill, 'Step 2', $step1);
        $step3 = Step::create($skill, 'Step 3', $step2);
        $step4 = Step::create($skill, 'Step 4', $step3);
        $this->resetDomainEvents();

        // Move step4 after step1
        // Before: 1, 2, 3, 4
        // After:  1, 4, 2, 3
        $skill->moveStepAfter($step4, $step1);

        self::assertSame(1, $step1->order);
        self::assertSame(2, $step4->order);
        self::assertSame(3, $step2->order);
        self::assertSame(4, $step3->order);

        $this->assertDomainEventRecorded(DirectiveUpdated::class);
    }

    public function testItShouldMoveStepToLastPosition(): void
    {
        $skill = Skill::draft(DirectiveId::fromString('my-skill'), 'My Skill', 'Description');
        $step1 = Step::create($skill, 'Step 1');
        $step2 = Step::create($skill, 'Step 2', $step1);
        $step3 = Step::create($skill, 'Step 3', $step2);
        $this->resetDomainEvents();

        // Move step1 after step3 (last position)
        $skill->moveStepAfter($step1, $step3);

        self::assertSame(3, $step1->order);
        self::assertSame(1, $step2->order);
        self::assertSame(2, $step3->order);

        $this->assertDomainEventRecorded(DirectiveUpdated::class);
    }

    public function testItShouldNotMoveStepAfterItself(): void
    {
        $skill = Skill::draft(DirectiveId::fromString('my-skill'), 'My Skill', 'Description');
        $step1 = Step::create($skill, 'Step 1');
        $step2 = Step::create($skill, 'Step 2', $step1);
        $this->resetDomainEvents();

        // Move step1 after itself (no-op)
        $skill->moveStepAfter($step1, $step1);

        self::assertSame(1, $step1->order);
        self::assertSame(2, $step2->order);

        $this->assertNoDomainEvents();
    }

    public function testItShouldNotMoveStepWhenAlreadyInPosition(): void
    {
        $skill = Skill::draft(DirectiveId::fromString('my-skill'), 'My Skill', 'Description');
        $step1 = Step::create($skill, 'Step 1');
        $step2 = Step::create($skill, 'Step 2', $step1);
        $step3 = Step::create($skill, 'Step 3', $step2);
        $this->resetDomainEvents();

        // Move step2 after step1 (already there)
        $skill->moveStepAfter($step2, $step1);

        self::assertSame(1, $step1->order);
        self::assertSame(2, $step2->order);
        self::assertSame(3, $step3->order);

        $this->assertNoDomainEvents();
    }

    // ========================================================================
    // STEP MANAGEMENT - INVALID OPERATIONS
    // ========================================================================

    public function testItShouldNotMoveStepFromAnotherSkill(): void
    {
        $skill1 = Skill::draft(DirectiveId::fromString('skill-one'), 'Skill 1', 'Description');
        $skill2 = Skill::draft(DirectiveId::fromString('skill-two'), 'Skill 2', 'Description');
        $stepFromSkill2 = Step::create($skill2, 'Step from skill 2');
        $this->resetDomainEvents();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Step does not belong to this skill.');

        $skill1->moveStepAfter($stepFromSkill2, null);
    }

    public function testItShouldNotMoveStepAfterReferenceFromAnotherSkill(): void
    {
        $skill1 = Skill::draft(DirectiveId::fromString('skill-one'), 'Skill 1', 'Description');
        $step1 = Step::create($skill1, 'Step 1');
        $skill2 = Skill::draft(DirectiveId::fromString('skill-two'), 'Skill 2', 'Description');
        $stepFromSkill2 = Step::create($skill2, 'Step from skill 2');
        $this->resetDomainEvents();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Reference step does not belong to this skill.');

        $skill1->moveStepAfter($step1, $stepFromSkill2);
    }

    public function testItShouldNotMoveStepOnArchivedSkill(): void
    {
        $skill = Skill::draft(DirectiveId::fromString('my-skill'), 'My Skill', 'Description');
        $step1 = Step::create($skill, 'Step 1');
        $step2 = Step::create($skill, 'Step 2', $step1);
        $skill->archive();
        $this->resetDomainEvents();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot perform this action on an archived directive.');

        $skill->moveStepAfter($step1, $step2);
    }
}
