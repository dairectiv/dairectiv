<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\Authoring\Application\Workflow;

use Dairectiv\Authoring\Application\Workflow\MoveStep\Input;
use Dairectiv\Authoring\Domain\Object\Directive\Event\DirectiveUpdated;
use Dairectiv\Authoring\Domain\Object\Directive\Exception\DirectiveNotFoundException;
use Dairectiv\Authoring\Domain\Object\Workflow\Step\Step;
use Dairectiv\Authoring\Domain\Object\Workflow\Workflow;
use Dairectiv\SharedKernel\Domain\Object\Exception\InvalidArgumentException;
use Dairectiv\Tests\Framework\IntegrationTestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('integration')]
#[Group('authoring')]
#[Group('use-case')]
final class MoveStepTest extends IntegrationTestCase
{
    public function testItShouldMoveStepToBeginning(): void
    {
        $workflow = self::draftWorkflow();
        $step1 = Step::create($workflow, 'Step 1');
        $step2 = Step::create($workflow, 'Step 2', $step1);
        $step3 = Step::create($workflow, 'Step 3', $step2);
        $this->persistEntity($workflow);

        $this->execute(new Input((string) $workflow->id, (string) $step3->id, null));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $persistedWorkflow = $this->findEntity(Workflow::class, ['id' => $workflow->id], true);

        $stepsOrdered = $persistedWorkflow->steps->toArray();
        self::assertSame('Step 3', $stepsOrdered[0]->content);
        self::assertSame(1, $stepsOrdered[0]->order);
        self::assertSame('Step 1', $stepsOrdered[1]->content);
        self::assertSame(2, $stepsOrdered[1]->order);
        self::assertSame('Step 2', $stepsOrdered[2]->content);
        self::assertSame(3, $stepsOrdered[2]->order);
    }

    public function testItShouldMoveStepAfterAnotherStep(): void
    {
        $workflow = self::draftWorkflow();
        $step1 = Step::create($workflow, 'Step 1');
        $step2 = Step::create($workflow, 'Step 2', $step1);
        $step3 = Step::create($workflow, 'Step 3', $step2);
        $this->persistEntity($workflow);

        $this->execute(new Input((string) $workflow->id, (string) $step3->id, (string) $step1->id));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $persistedWorkflow = $this->findEntity(Workflow::class, ['id' => $workflow->id], true);

        $stepsOrdered = $persistedWorkflow->steps->toArray();
        self::assertSame('Step 1', $stepsOrdered[0]->content);
        self::assertSame(1, $stepsOrdered[0]->order);
        self::assertSame('Step 3', $stepsOrdered[1]->content);
        self::assertSame(2, $stepsOrdered[1]->order);
        self::assertSame('Step 2', $stepsOrdered[2]->content);
        self::assertSame(3, $stepsOrdered[2]->order);
    }

    public function testItShouldMoveFirstStepToEnd(): void
    {
        $workflow = self::draftWorkflow();
        $step1 = Step::create($workflow, 'Step 1');
        $step2 = Step::create($workflow, 'Step 2', $step1);
        $step3 = Step::create($workflow, 'Step 3', $step2);
        $this->persistEntity($workflow);

        $this->execute(new Input((string) $workflow->id, (string) $step1->id, (string) $step3->id));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $persistedWorkflow = $this->findEntity(Workflow::class, ['id' => $workflow->id], true);

        $stepsOrdered = $persistedWorkflow->steps->toArray();
        self::assertSame('Step 2', $stepsOrdered[0]->content);
        self::assertSame(1, $stepsOrdered[0]->order);
        self::assertSame('Step 3', $stepsOrdered[1]->content);
        self::assertSame(2, $stepsOrdered[1]->order);
        self::assertSame('Step 1', $stepsOrdered[2]->content);
        self::assertSame(3, $stepsOrdered[2]->order);
    }

    public function testItShouldDoNothingWhenMovingAfterItself(): void
    {
        $workflow = self::draftWorkflow();
        $step1 = Step::create($workflow, 'Step 1');
        $step2 = Step::create($workflow, 'Step 2', $step1);
        Step::create($workflow, 'Step 3', $step2);
        $this->persistEntity($workflow);

        $this->execute(new Input((string) $workflow->id, (string) $step2->id, (string) $step2->id));

        // No domain event should be dispatched since nothing changed
        $persistedWorkflow = $this->findEntity(Workflow::class, ['id' => $workflow->id], true);

        $stepsOrdered = $persistedWorkflow->steps->toArray();
        self::assertSame('Step 1', $stepsOrdered[0]->content);
        self::assertSame(1, $stepsOrdered[0]->order);
        self::assertSame('Step 2', $stepsOrdered[1]->content);
        self::assertSame(2, $stepsOrdered[1]->order);
        self::assertSame('Step 3', $stepsOrdered[2]->content);
        self::assertSame(3, $stepsOrdered[2]->order);
    }

    public function testItShouldDoNothingWhenStepAlreadyInPosition(): void
    {
        $workflow = self::draftWorkflow();
        $step1 = Step::create($workflow, 'Step 1');
        $step2 = Step::create($workflow, 'Step 2', $step1);
        Step::create($workflow, 'Step 3', $step2);
        $this->persistEntity($workflow);

        $this->execute(new Input((string) $workflow->id, (string) $step2->id, (string) $step1->id));

        // No domain event should be dispatched since nothing changed
        $persistedWorkflow = $this->findEntity(Workflow::class, ['id' => $workflow->id], true);

        $stepsOrdered = $persistedWorkflow->steps->toArray();
        self::assertSame('Step 1', $stepsOrdered[0]->content);
        self::assertSame(1, $stepsOrdered[0]->order);
        self::assertSame('Step 2', $stepsOrdered[1]->content);
        self::assertSame(2, $stepsOrdered[1]->order);
        self::assertSame('Step 3', $stepsOrdered[2]->content);
        self::assertSame(3, $stepsOrdered[2]->order);
    }

    public function testItShouldThrowExceptionWhenWorkflowNotFound(): void
    {
        $this->expectException(DirectiveNotFoundException::class);

        $this->execute(new Input(
            'non-existent-workflow',
            '00000000-0000-0000-0000-000000000000',
            null,
        ));
    }

    public function testItShouldThrowExceptionWhenStepNotFound(): void
    {
        $workflow = self::draftWorkflow();
        $this->persistEntity($workflow);

        $nonExistentId = '00000000-0000-0000-0000-000000000000';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(\sprintf('Step with ID "%s" not found.', $nonExistentId));

        $this->execute(new Input((string) $workflow->id, $nonExistentId, null));
    }

    public function testItShouldThrowExceptionWhenReferenceStepNotFound(): void
    {
        $workflow = self::draftWorkflow();
        $step = Step::create($workflow, 'Step');
        $this->persistEntity($workflow);

        $nonExistentId = '00000000-0000-0000-0000-000000000000';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(\sprintf('Reference step with ID "%s" not found.', $nonExistentId));

        $this->execute(new Input((string) $workflow->id, (string) $step->id, $nonExistentId));
    }

    public function testItShouldThrowExceptionWhenWorkflowIsArchived(): void
    {
        $workflow = self::draftWorkflow();
        $step1 = Step::create($workflow, 'Step 1');
        $step2 = Step::create($workflow, 'Step 2', $step1);
        $workflow->archive();
        $this->persistEntity($workflow);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot perform this action on an archived directive.');

        $this->execute(new Input((string) $workflow->id, (string) $step2->id, null));
    }

    public function testItShouldMoveMiddleStepForward(): void
    {
        $workflow = self::draftWorkflow();
        $step1 = Step::create($workflow, 'Step 1');
        $step2 = Step::create($workflow, 'Step 2', $step1);
        $step3 = Step::create($workflow, 'Step 3', $step2);
        Step::create($workflow, 'Step 4', $step3);
        $this->persistEntity($workflow);

        $this->execute(new Input((string) $workflow->id, (string) $step2->id, (string) $step3->id));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $persistedWorkflow = $this->findEntity(Workflow::class, ['id' => $workflow->id], true);

        $stepsOrdered = $persistedWorkflow->steps->toArray();
        self::assertSame('Step 1', $stepsOrdered[0]->content);
        self::assertSame(1, $stepsOrdered[0]->order);
        self::assertSame('Step 3', $stepsOrdered[1]->content);
        self::assertSame(2, $stepsOrdered[1]->order);
        self::assertSame('Step 2', $stepsOrdered[2]->content);
        self::assertSame(3, $stepsOrdered[2]->order);
        self::assertSame('Step 4', $stepsOrdered[3]->content);
        self::assertSame(4, $stepsOrdered[3]->order);
    }

    public function testItShouldMoveMiddleStepBackward(): void
    {
        $workflow = self::draftWorkflow();
        $step1 = Step::create($workflow, 'Step 1');
        $step2 = Step::create($workflow, 'Step 2', $step1);
        $step3 = Step::create($workflow, 'Step 3', $step2);
        Step::create($workflow, 'Step 4', $step3);
        $this->persistEntity($workflow);

        $this->execute(new Input((string) $workflow->id, (string) $step3->id, null));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $persistedWorkflow = $this->findEntity(Workflow::class, ['id' => $workflow->id], true);

        $stepsOrdered = $persistedWorkflow->steps->toArray();
        self::assertSame('Step 3', $stepsOrdered[0]->content);
        self::assertSame(1, $stepsOrdered[0]->order);
        self::assertSame('Step 1', $stepsOrdered[1]->content);
        self::assertSame(2, $stepsOrdered[1]->order);
        self::assertSame('Step 2', $stepsOrdered[2]->content);
        self::assertSame(3, $stepsOrdered[2]->order);
        self::assertSame('Step 4', $stepsOrdered[3]->content);
        self::assertSame(4, $stepsOrdered[3]->order);
    }
}
