<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\Authoring\Application\Workflow;

use Cake\Chronos\Chronos;
use Dairectiv\Authoring\Application\Workflow\AddStep\Input;
use Dairectiv\Authoring\Application\Workflow\AddStep\Output;
use Dairectiv\Authoring\Domain\Object\Directive\Event\DirectiveUpdated;
use Dairectiv\Authoring\Domain\Object\Workflow\Exception\WorkflowNotFoundException;
use Dairectiv\Authoring\Domain\Object\Workflow\Step\Step;
use Dairectiv\Authoring\Domain\Object\Workflow\Workflow;
use Dairectiv\SharedKernel\Domain\Object\Exception\InvalidArgumentException;
use Dairectiv\Tests\Framework\IntegrationTestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('integration')]
#[Group('authoring')]
#[Group('use-case')]
final class AddStepTest extends IntegrationTestCase
{
    public function testItShouldAddFirstStepToWorkflow(): void
    {
        $workflow = self::draftWorkflow();
        $this->persistEntity($workflow);

        self::assertCount(0, $workflow->steps);

        $output = $this->execute(new Input((string) $workflow->id, 'Step 1 content'));

        self::assertInstanceOf(Output::class, $output);
        self::assertSame('Step 1 content', $output->step->content);
        self::assertSame(1, $output->step->order);

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $persistedWorkflow = $this->findEntity(Workflow::class, ['id' => $workflow->id], true);

        self::assertCount(1, $persistedWorkflow->steps);
        $persistedStep = $persistedWorkflow->steps->first();
        self::assertInstanceOf(Step::class, $persistedStep);
        self::assertSame('Step 1 content', $persistedStep->content);
        self::assertSame(1, $persistedStep->order);
    }

    public function testItShouldAddMultipleStepsInOrder(): void
    {
        $workflow = self::draftWorkflow();
        $this->persistEntity($workflow);

        $output1 = $this->execute(new Input((string) $workflow->id, 'Step 1'));
        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $output2 = $this->execute(new Input((string) $workflow->id, 'Step 2'));
        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $output3 = $this->execute(new Input((string) $workflow->id, 'Step 3'));
        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        self::assertInstanceOf(Output::class, $output1);
        self::assertSame(1, $output1->step->order);

        self::assertInstanceOf(Output::class, $output2);
        self::assertSame(1, $output2->step->order);

        self::assertInstanceOf(Output::class, $output3);
        self::assertSame(1, $output3->step->order);

        $persistedWorkflow = $this->findEntity(Workflow::class, ['id' => $workflow->id], true);

        self::assertCount(3, $persistedWorkflow->steps);

        $stepsOrdered = $persistedWorkflow->steps->toArray();
        self::assertSame('Step 3', $stepsOrdered[0]->content);
        self::assertSame(1, $stepsOrdered[0]->order);
        self::assertSame('Step 2', $stepsOrdered[1]->content);
        self::assertSame(2, $stepsOrdered[1]->order);
        self::assertSame('Step 1', $stepsOrdered[2]->content);
        self::assertSame(3, $stepsOrdered[2]->order);
    }

    public function testItShouldInsertStepAfterSpecificStep(): void
    {
        $workflow = self::draftWorkflow();
        $step1 = Step::create($workflow, 'Step 1');
        Step::create($workflow, 'Step 2', $step1);
        $this->persistEntity($workflow);

        self::assertCount(2, $workflow->steps);

        $this->execute(new Input(
            (string) $workflow->id,
            'Inserted Step',
            (string) $step1->id,
        ));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $persistedWorkflow = $this->findEntity(Workflow::class, ['id' => $workflow->id], true);

        self::assertCount(3, $persistedWorkflow->steps);

        $stepsOrdered = $persistedWorkflow->steps->toArray();
        self::assertSame('Step 1', $stepsOrdered[0]->content);
        self::assertSame(1, $stepsOrdered[0]->order);
        self::assertSame('Inserted Step', $stepsOrdered[1]->content);
        self::assertSame(2, $stepsOrdered[1]->order);
        self::assertSame('Step 2', $stepsOrdered[2]->content);
        self::assertSame(3, $stepsOrdered[2]->order);
    }

    public function testItShouldInsertStepAtEnd(): void
    {
        $workflow = self::draftWorkflow();
        $step1 = Step::create($workflow, 'Step 1');
        $step2 = Step::create($workflow, 'Step 2', $step1);
        $this->persistEntity($workflow);

        $this->execute(new Input(
            (string) $workflow->id,
            'Last Step',
            (string) $step2->id,
        ));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $persistedWorkflow = $this->findEntity(Workflow::class, ['id' => $workflow->id], true);

        self::assertCount(3, $persistedWorkflow->steps);

        $stepsOrdered = $persistedWorkflow->steps->toArray();
        self::assertSame('Step 1', $stepsOrdered[0]->content);
        self::assertSame(1, $stepsOrdered[0]->order);
        self::assertSame('Step 2', $stepsOrdered[1]->content);
        self::assertSame(2, $stepsOrdered[1]->order);
        self::assertSame('Last Step', $stepsOrdered[2]->content);
        self::assertSame(3, $stepsOrdered[2]->order);
    }

    public function testItShouldThrowExceptionWhenWorkflowNotFound(): void
    {
        $this->expectException(WorkflowNotFoundException::class);

        $this->execute(new Input('non-existent-workflow', 'Step content'));
    }

    public function testItShouldThrowExceptionWhenAfterStepNotFound(): void
    {
        $workflow = self::draftWorkflow();
        $this->persistEntity($workflow);

        $nonExistentId = '00000000-0000-0000-0000-000000000000';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(\sprintf('Step with ID "%s" not found.', $nonExistentId));

        $this->execute(new Input((string) $workflow->id, 'Step content', $nonExistentId));
    }

    public function testItShouldThrowExceptionWhenWorkflowIsArchived(): void
    {
        $workflow = self::draftWorkflow();
        $workflow->archive();
        $this->persistEntity($workflow);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot perform this action on an archived directive.');

        $this->execute(new Input((string) $workflow->id, 'Step content'));
    }

    public function testItShouldPersistStepWithCorrectTimestamps(): void
    {
        $workflow = self::draftWorkflow();
        $this->persistEntity($workflow);

        Chronos::setTestNow(Chronos::now()->addDays(1));

        $output = $this->execute(new Input((string) $workflow->id, 'Step content'));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        self::assertInstanceOf(Output::class, $output);
        self::assertTrue(Chronos::now()->equals($output->step->createdAt));
        self::assertTrue(Chronos::now()->equals($output->step->updatedAt));

        $persistedWorkflow = $this->findEntity(Workflow::class, ['id' => $workflow->id], true);
        $persistedStep = $persistedWorkflow->steps->first();

        self::assertInstanceOf(Step::class, $persistedStep);
        self::assertTrue($persistedWorkflow->createdAt->lessThan($persistedStep->createdAt));
        self::assertTrue($persistedWorkflow->createdAt->lessThan($persistedStep->updatedAt));
    }

    public function testItShouldGenerateUniqueStepId(): void
    {
        $workflow = self::draftWorkflow();
        $this->persistEntity($workflow);

        $output1 = $this->execute(new Input((string) $workflow->id, 'Step 1'));
        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);
        self::assertInstanceOf(Output::class, $output1);

        $output2 = $this->execute(new Input((string) $workflow->id, 'Step 2'));
        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);
        self::assertInstanceOf(Output::class, $output2);

        self::assertFalse($output1->step->id->equals($output2->step->id));
    }

    public function testItShouldLinkStepToCorrectWorkflow(): void
    {
        $workflow = self::draftWorkflow();
        $this->persistEntity($workflow);

        $output = $this->execute(new Input((string) $workflow->id, 'Step content'));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        self::assertInstanceOf(Output::class, $output);
        self::assertTrue($output->step->workflow->id->equals($workflow->id));
    }
}
