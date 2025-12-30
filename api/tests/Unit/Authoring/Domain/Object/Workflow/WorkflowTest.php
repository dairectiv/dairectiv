<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Unit\Authoring\Domain\Object\Workflow;

use Cake\Chronos\Chronos;
use Dairectiv\Authoring\Domain\Object\Directive\DirectiveId;
use Dairectiv\Authoring\Domain\Object\Directive\DirectiveState;
use Dairectiv\Authoring\Domain\Object\Directive\Event\DirectiveArchived;
use Dairectiv\Authoring\Domain\Object\Directive\Event\DirectiveDrafted;
use Dairectiv\Authoring\Domain\Object\Directive\Event\DirectivePublished;
use Dairectiv\Authoring\Domain\Object\Directive\Event\DirectiveUpdated;
use Dairectiv\Authoring\Domain\Object\Workflow\Example\Example;
use Dairectiv\Authoring\Domain\Object\Workflow\Example\ExampleId;
use Dairectiv\Authoring\Domain\Object\Workflow\Step\Step;
use Dairectiv\Authoring\Domain\Object\Workflow\Step\StepId;
use Dairectiv\Authoring\Domain\Object\Workflow\Workflow;
use Dairectiv\SharedKernel\Domain\Object\Exception\InvalidArgumentException;
use Dairectiv\Tests\Framework\UnitTestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('authoring')]
#[Group('unit')]
final class WorkflowTest extends UnitTestCase
{
    // ========================================================================
    // WORKFLOW LIFECYCLE
    // ========================================================================

    public function testItShouldDraftWorkflow(): void
    {
        $id = DirectiveId::fromString('my-workflow');

        $workflow = Workflow::draft($id, 'My Workflow', 'A description of my workflow');

        self::assertSame($id, $workflow->id);
        self::assertSame('my-workflow', (string) $workflow->id);
        self::assertSame('My Workflow', $workflow->name);
        self::assertSame('A description of my workflow', $workflow->description);
        self::assertSame(DirectiveState::Draft, $workflow->state);
        self::assertTrue($workflow->examples->isEmpty());
        self::assertTrue($workflow->steps->isEmpty());
        self::assertEquals(Chronos::now(), $workflow->createdAt);
        self::assertEquals(Chronos::now(), $workflow->updatedAt);

        $this->assertDomainEventRecorded(DirectiveDrafted::class);
    }

    public function testItShouldUpdateMetadata(): void
    {
        $workflow = Workflow::draft(DirectiveId::fromString('my-workflow'), 'Original Name', 'Original Description');
        $this->resetDomainEvents();

        Chronos::setTestNow(Chronos::now()->addMinutes(1));
        $workflow->updateMetadata('Updated Name', 'Updated Description');

        self::assertSame('Updated Name', $workflow->name);
        self::assertSame('Updated Description', $workflow->description);
        self::assertEquals(Chronos::now(), $workflow->updatedAt);

        $this->assertDomainEventRecorded(DirectiveUpdated::class);
    }

    public function testItShouldPublishWorkflow(): void
    {
        $workflow = Workflow::draft(DirectiveId::fromString('my-workflow'), 'My Workflow', 'Description');
        $this->resetDomainEvents();

        Chronos::setTestNow(Chronos::now()->addMinutes(1));
        $workflow->publish();

        self::assertSame(DirectiveState::Published, $workflow->state);
        self::assertEquals(Chronos::now(), $workflow->updatedAt);

        $this->assertDomainEventRecorded(DirectivePublished::class);
    }

    public function testItShouldArchiveWorkflow(): void
    {
        $workflow = Workflow::draft(DirectiveId::fromString('my-workflow'), 'My Workflow', 'Description');
        $workflow->publish();
        $this->resetDomainEvents();

        Chronos::setTestNow(Chronos::now()->addMinutes(1));
        $workflow->archive();

        self::assertSame(DirectiveState::Archived, $workflow->state);
        self::assertEquals(Chronos::now(), $workflow->updatedAt);

        $this->assertDomainEventRecorded(DirectiveArchived::class);
    }

    public function testItShouldUpdateContent(): void
    {
        $workflow = Workflow::draft(DirectiveId::fromString('my-workflow'), 'My Workflow', 'Description');
        $this->resetDomainEvents();

        Chronos::setTestNow(Chronos::now()->addMinutes(1));
        $workflow->updateContent('New content for the workflow');

        self::assertSame('New content for the workflow', $workflow->content);
        self::assertEquals(Chronos::now(), $workflow->updatedAt);

        $this->assertDomainEventRecorded(DirectiveUpdated::class);
    }

    // ========================================================================
    // WORKFLOW LIFECYCLE - INVALID STATE TRANSITIONS
    // ========================================================================

    public function testItShouldNotPublishAlreadyPublishedWorkflow(): void
    {
        $workflow = Workflow::draft(DirectiveId::fromString('my-workflow'), 'My Workflow', 'Description');
        $workflow->publish();
        $this->resetDomainEvents();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Only draft directives can be published.');

        $workflow->publish();
    }

    public function testItShouldNotPublishArchivedWorkflow(): void
    {
        $workflow = Workflow::draft(DirectiveId::fromString('my-workflow'), 'My Workflow', 'Description');
        $workflow->archive();
        $this->resetDomainEvents();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Only draft directives can be published.');

        $workflow->publish();
    }

    public function testItShouldNotArchiveAlreadyArchivedWorkflow(): void
    {
        $workflow = Workflow::draft(DirectiveId::fromString('my-workflow'), 'My Workflow', 'Description');
        $workflow->archive();
        $this->resetDomainEvents();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Directive is already archived.');

        $workflow->archive();
    }

    public function testItShouldNotUpdateMetadataOfArchivedWorkflow(): void
    {
        $workflow = Workflow::draft(DirectiveId::fromString('my-workflow'), 'My Workflow', 'Description');
        $workflow->archive();
        $this->resetDomainEvents();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot perform this action on an archived directive.');

        $workflow->updateMetadata('New Name', 'New Description');
    }

    public function testItShouldNotUpdateMetadataWithNullFields(): void
    {
        $workflow = Workflow::draft(DirectiveId::fromString('my-workflow'), 'My Workflow', 'Description');
        $this->resetDomainEvents();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('At least one metadata field must be provided.');

        $workflow->updateMetadata(null, null);
    }

    public function testItShouldNotUpdateContentOfArchivedWorkflow(): void
    {
        $workflow = Workflow::draft(DirectiveId::fromString('my-workflow'), 'My Workflow', 'Description');
        $workflow->archive();
        $this->resetDomainEvents();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot perform this action on an archived directive.');

        $workflow->updateContent('New content');
    }

    public function testItShouldNotAddExampleToArchivedWorkflow(): void
    {
        $workflow = Workflow::draft(DirectiveId::fromString('my-workflow'), 'My Workflow', 'Description');
        $workflow->archive();
        $this->resetDomainEvents();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot perform this action on an archived directive.');

        Example::create($workflow, 'Scenario', 'Input', 'Output', 'Explanation');
    }

    public function testItShouldNotAddStepToArchivedWorkflow(): void
    {
        $workflow = Workflow::draft(DirectiveId::fromString('my-workflow'), 'My Workflow', 'Description');
        $workflow->archive();
        $this->resetDomainEvents();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot perform this action on an archived directive.');

        Step::create($workflow, 'Step content');
    }

    // ========================================================================
    // EXAMPLE MANAGEMENT
    // ========================================================================

    public function testItShouldAddExample(): void
    {
        $workflow = Workflow::draft(DirectiveId::fromString('my-workflow'), 'My Workflow', 'Description');
        $this->resetDomainEvents();

        Chronos::setTestNow(Chronos::now()->addMinutes(1));

        $exampleId = ExampleId::setNext();

        $example = Example::create($workflow, 'Scenario', 'Input', 'Output', 'Explanation');

        self::assertTrue($exampleId->equals($example->id));
        self::assertSame('Scenario', $example->scenario);
        self::assertSame('Input', $example->input);
        self::assertSame('Output', $example->output);
        self::assertSame('Explanation', $example->explanation);
        self::assertSame($workflow, $example->workflow);
        self::assertEquals(Chronos::now(), $example->createdAt);
        self::assertEquals(Chronos::now(), $example->updatedAt);

        self::assertEquals(Chronos::now(), $workflow->updatedAt);
        self::assertTrue($workflow->examples->contains($example));

        $this->assertDomainEventRecorded(DirectiveUpdated::class);
    }

    public function testItShouldAddExampleWithoutExplanation(): void
    {
        $workflow = Workflow::draft(DirectiveId::fromString('my-workflow'), 'My Workflow', 'Description');
        $this->resetDomainEvents();

        Chronos::setTestNow(Chronos::now()->addMinutes(1));

        $example = Example::create($workflow, 'Scenario', 'Input', 'Output');

        self::assertSame('Scenario', $example->scenario);
        self::assertSame('Input', $example->input);
        self::assertSame('Output', $example->output);
        self::assertNull($example->explanation);
        self::assertSame($workflow, $example->workflow);

        self::assertTrue($workflow->examples->contains($example));

        $this->assertDomainEventRecorded(DirectiveUpdated::class);
    }

    public function testItShouldAddMultipleExamples(): void
    {
        $workflow = Workflow::draft(DirectiveId::fromString('my-workflow'), 'My Workflow', 'Description');
        $this->resetDomainEvents();

        $example1 = Example::create($workflow, 'Scenario 1', 'Input 1', 'Output 1');
        $example2 = Example::create($workflow, 'Scenario 2', 'Input 2', 'Output 2');
        $example3 = Example::create($workflow, 'Scenario 3', 'Input 3', 'Output 3');

        self::assertCount(3, $workflow->examples);
        self::assertTrue($workflow->examples->contains($example1));
        self::assertTrue($workflow->examples->contains($example2));
        self::assertTrue($workflow->examples->contains($example3));

        $this->assertDomainEventRecorded(DirectiveUpdated::class, 3);
    }

    // ========================================================================
    // STEP MANAGEMENT - CREATION
    // ========================================================================

    public function testItShouldCreateFirstStep(): void
    {
        $workflow = Workflow::draft(DirectiveId::fromString('my-workflow'), 'My Workflow', 'Description');
        $this->resetDomainEvents();

        Chronos::setTestNow(Chronos::now()->addMinutes(1));

        $stepId = StepId::setNext();

        // First step (null on empty workflow means position 1)
        $step = Step::create($workflow, 'First step content');

        self::assertTrue($stepId->equals($step->id));
        self::assertSame(1, $step->order);
        self::assertSame('First step content', $step->content);
        self::assertSame($workflow, $step->workflow);
        self::assertEquals(Chronos::now(), $step->createdAt);
        self::assertEquals(Chronos::now(), $step->updatedAt);

        self::assertEquals(Chronos::now(), $workflow->updatedAt);
        self::assertTrue($workflow->steps->contains($step));

        $this->assertDomainEventRecorded(DirectiveUpdated::class);
    }

    public function testItShouldCreateStepAtEnd(): void
    {
        $workflow = Workflow::draft(DirectiveId::fromString('my-workflow'), 'My Workflow', 'Description');
        $step1 = Step::create($workflow, 'First step');
        $this->resetDomainEvents();

        // Create at the end by passing the last step
        $step2 = Step::create($workflow, 'Second step', $step1);

        self::assertSame(1, $step1->order);
        self::assertSame(2, $step2->order);

        $this->assertDomainEventRecorded(DirectiveUpdated::class);
    }

    public function testItShouldCreateStepAtBeginning(): void
    {
        $workflow = Workflow::draft(DirectiveId::fromString('my-workflow'), 'My Workflow', 'Description');
        $step1 = Step::create($workflow, 'First step');
        $step2 = Step::create($workflow, 'Second step', $step1);
        $this->resetDomainEvents();

        // Create at the beginning (null)
        $step3 = Step::create($workflow, 'New first step', null);

        self::assertSame(1, $step3->order);
        self::assertSame(2, $step1->order);
        self::assertSame(3, $step2->order);

        $this->assertDomainEventRecorded(DirectiveUpdated::class);
    }

    public function testItShouldCreateStepInMiddle(): void
    {
        $workflow = Workflow::draft(DirectiveId::fromString('my-workflow'), 'My Workflow', 'Description');
        $step1 = Step::create($workflow, 'Step 1');
        $step2 = Step::create($workflow, 'Step 2', $step1);
        $step3 = Step::create($workflow, 'Step 3', $step2);
        $this->resetDomainEvents();

        // Insert after step1 (in the middle)
        $step4 = Step::create($workflow, 'Step 1.5', $step1);

        self::assertSame(1, $step1->order);
        self::assertSame(2, $step4->order);
        self::assertSame(3, $step2->order);
        self::assertSame(4, $step3->order);

        $this->assertDomainEventRecorded(DirectiveUpdated::class);
    }

    public function testItShouldCreateMultipleStepsSequentially(): void
    {
        $workflow = Workflow::draft(DirectiveId::fromString('my-workflow'), 'My Workflow', 'Description');
        $this->resetDomainEvents();

        $step1 = Step::create($workflow, 'First step');
        $step2 = Step::create($workflow, 'Second step', $step1);
        $step3 = Step::create($workflow, 'Third step', $step2);

        self::assertCount(3, $workflow->steps);
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
        $workflow = Workflow::draft(DirectiveId::fromString('my-workflow'), 'My Workflow', 'Description');
        $step = Step::create($workflow, 'Original content');
        $this->resetDomainEvents();

        Chronos::setTestNow(Chronos::now()->addMinutes(1));

        $step->update('Updated content');

        self::assertSame('Updated content', $step->content);
        self::assertEquals(Chronos::now(), $step->updatedAt);
        self::assertEquals(Chronos::now(), $workflow->updatedAt);

        $this->assertDomainEventRecorded(DirectiveUpdated::class);
    }

    // ========================================================================
    // STEP MANAGEMENT - MOVE
    // ========================================================================

    public function testItShouldMoveStepAfterAnother(): void
    {
        $workflow = Workflow::draft(DirectiveId::fromString('my-workflow'), 'My Workflow', 'Description');
        $step1 = Step::create($workflow, 'Step 1');
        $step2 = Step::create($workflow, 'Step 2', $step1);
        $step3 = Step::create($workflow, 'Step 3', $step2);
        $step4 = Step::create($workflow, 'Step 4', $step3);
        $this->resetDomainEvents();

        // Move step1 after step3
        // Before: 1, 2, 3, 4
        // After:  2, 3, 1, 4
        $workflow->moveStepAfter($step1, $step3);

        self::assertSame(3, $step1->order);
        self::assertSame(1, $step2->order);
        self::assertSame(2, $step3->order);
        self::assertSame(4, $step4->order);

        $this->assertDomainEventRecorded(DirectiveUpdated::class);
    }

    public function testItShouldMoveStepToFirstPosition(): void
    {
        $workflow = Workflow::draft(DirectiveId::fromString('my-workflow'), 'My Workflow', 'Description');
        $step1 = Step::create($workflow, 'Step 1');
        $step2 = Step::create($workflow, 'Step 2', $step1);
        $step3 = Step::create($workflow, 'Step 3', $step2);
        $this->resetDomainEvents();

        // Move step3 to first position (after null)
        $workflow->moveStepAfter($step3, null);

        self::assertSame(1, $step3->order);
        self::assertSame(2, $step1->order);
        self::assertSame(3, $step2->order);

        $this->assertDomainEventRecorded(DirectiveUpdated::class);
    }

    public function testItShouldMoveStepBackward(): void
    {
        $workflow = Workflow::draft(DirectiveId::fromString('my-workflow'), 'My Workflow', 'Description');
        $step1 = Step::create($workflow, 'Step 1');
        $step2 = Step::create($workflow, 'Step 2', $step1);
        $step3 = Step::create($workflow, 'Step 3', $step2);
        $step4 = Step::create($workflow, 'Step 4', $step3);
        $this->resetDomainEvents();

        // Move step4 after step1
        // Before: 1, 2, 3, 4
        // After:  1, 4, 2, 3
        $workflow->moveStepAfter($step4, $step1);

        self::assertSame(1, $step1->order);
        self::assertSame(2, $step4->order);
        self::assertSame(3, $step2->order);
        self::assertSame(4, $step3->order);

        $this->assertDomainEventRecorded(DirectiveUpdated::class);
    }

    public function testItShouldMoveStepToLastPosition(): void
    {
        $workflow = Workflow::draft(DirectiveId::fromString('my-workflow'), 'My Workflow', 'Description');
        $step1 = Step::create($workflow, 'Step 1');
        $step2 = Step::create($workflow, 'Step 2', $step1);
        $step3 = Step::create($workflow, 'Step 3', $step2);
        $this->resetDomainEvents();

        // Move step1 after step3 (last position)
        $workflow->moveStepAfter($step1, $step3);

        self::assertSame(3, $step1->order);
        self::assertSame(1, $step2->order);
        self::assertSame(2, $step3->order);

        $this->assertDomainEventRecorded(DirectiveUpdated::class);
    }

    public function testItShouldNotMoveStepAfterItself(): void
    {
        $workflow = Workflow::draft(DirectiveId::fromString('my-workflow'), 'My Workflow', 'Description');
        $step1 = Step::create($workflow, 'Step 1');
        $step2 = Step::create($workflow, 'Step 2', $step1);
        $this->resetDomainEvents();

        // Move step1 after itself (no-op)
        $workflow->moveStepAfter($step1, $step1);

        self::assertSame(1, $step1->order);
        self::assertSame(2, $step2->order);

        $this->assertNoDomainEvents();
    }

    public function testItShouldNotMoveStepWhenAlreadyInPosition(): void
    {
        $workflow = Workflow::draft(DirectiveId::fromString('my-workflow'), 'My Workflow', 'Description');
        $step1 = Step::create($workflow, 'Step 1');
        $step2 = Step::create($workflow, 'Step 2', $step1);
        $step3 = Step::create($workflow, 'Step 3', $step2);
        $this->resetDomainEvents();

        // Move step2 after step1 (already there)
        $workflow->moveStepAfter($step2, $step1);

        self::assertSame(1, $step1->order);
        self::assertSame(2, $step2->order);
        self::assertSame(3, $step3->order);

        $this->assertNoDomainEvents();
    }

    // ========================================================================
    // STEP MANAGEMENT - INVALID OPERATIONS
    // ========================================================================

    public function testItShouldNotMoveStepFromAnotherWorkflow(): void
    {
        $workflow1 = Workflow::draft(DirectiveId::fromString('workflow-one'), 'Workflow 1', 'Description');
        $workflow2 = Workflow::draft(DirectiveId::fromString('workflow-two'), 'Workflow 2', 'Description');
        $stepFromWorkflow2 = Step::create($workflow2, 'Step from workflow 2');
        $this->resetDomainEvents();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Step does not belong to this workflow.');

        $workflow1->moveStepAfter($stepFromWorkflow2, null);
    }

    public function testItShouldNotMoveStepAfterReferenceFromAnotherWorkflow(): void
    {
        $workflow1 = Workflow::draft(DirectiveId::fromString('workflow-one'), 'Workflow 1', 'Description');
        $step1 = Step::create($workflow1, 'Step 1');
        $workflow2 = Workflow::draft(DirectiveId::fromString('workflow-two'), 'Workflow 2', 'Description');
        $stepFromWorkflow2 = Step::create($workflow2, 'Step from workflow 2');
        $this->resetDomainEvents();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Reference step does not belong to this workflow.');

        $workflow1->moveStepAfter($step1, $stepFromWorkflow2);
    }

    public function testItShouldNotMoveStepOnArchivedWorkflow(): void
    {
        $workflow = Workflow::draft(DirectiveId::fromString('my-workflow'), 'My Workflow', 'Description');
        $step1 = Step::create($workflow, 'Step 1');
        $step2 = Step::create($workflow, 'Step 2', $step1);
        $workflow->archive();
        $this->resetDomainEvents();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot perform this action on an archived directive.');

        $workflow->moveStepAfter($step1, $step2);
    }
}
