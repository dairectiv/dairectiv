<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\Authoring\Application\Workflow;

use Dairectiv\Authoring\Application\Workflow\RemoveStep\Input;
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
final class RemoveStepTest extends IntegrationTestCase
{
    public function testItShouldRemoveStepFromWorkflow(): void
    {
        $workflow = self::draftWorkflowEntity();
        $step = Step::create($workflow, 'Step content');
        $this->persistEntity($workflow);

        self::assertCount(1, $workflow->steps);

        $this->execute(new Input((string) $workflow->id, (string) $step->id));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $persistedWorkflow = $this->findEntity(Workflow::class, ['id' => $workflow->id], true);

        self::assertCount(0, $persistedWorkflow->steps);
    }

    public function testItShouldRemoveOneStepFromMultiple(): void
    {
        $workflow = self::draftWorkflowEntity();
        $step1 = Step::create($workflow, 'Step 1');
        $step2 = Step::create($workflow, 'Step 2', $step1);
        Step::create($workflow, 'Step 3', $step2);
        $this->persistEntity($workflow);

        self::assertCount(3, $workflow->steps);

        $this->execute(new Input((string) $workflow->id, (string) $step2->id));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $persistedWorkflow = $this->findEntity(Workflow::class, ['id' => $workflow->id], true);

        self::assertCount(2, $persistedWorkflow->steps);

        $stepsOrdered = $persistedWorkflow->steps->toArray();
        self::assertSame('Step 1', $stepsOrdered[0]->content);
        self::assertSame('Step 3', $stepsOrdered[1]->content);
    }

    public function testItShouldReorderRemainingSteps(): void
    {
        $workflow = self::draftWorkflowEntity();
        $step1 = Step::create($workflow, 'Step 1');
        $step2 = Step::create($workflow, 'Step 2', $step1);
        Step::create($workflow, 'Step 3', $step2);
        $this->persistEntity($workflow);

        $this->execute(new Input((string) $workflow->id, (string) $step2->id));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $persistedWorkflow = $this->findEntity(Workflow::class, ['id' => $workflow->id], true);

        $stepsOrdered = $persistedWorkflow->steps->toArray();
        self::assertSame(1, $stepsOrdered[0]->order);
        self::assertSame(2, $stepsOrdered[1]->order);
    }

    public function testItShouldRemoveFirstStep(): void
    {
        $workflow = self::draftWorkflowEntity();
        $step1 = Step::create($workflow, 'Step 1');
        $step2 = Step::create($workflow, 'Step 2', $step1);
        Step::create($workflow, 'Step 3', $step2);
        $this->persistEntity($workflow);

        $this->execute(new Input((string) $workflow->id, (string) $step1->id));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $persistedWorkflow = $this->findEntity(Workflow::class, ['id' => $workflow->id], true);

        self::assertCount(2, $persistedWorkflow->steps);

        $stepsOrdered = $persistedWorkflow->steps->toArray();
        self::assertSame('Step 2', $stepsOrdered[0]->content);
        self::assertSame(1, $stepsOrdered[0]->order);
        self::assertSame('Step 3', $stepsOrdered[1]->content);
        self::assertSame(2, $stepsOrdered[1]->order);
    }

    public function testItShouldRemoveLastStep(): void
    {
        $workflow = self::draftWorkflowEntity();
        $step1 = Step::create($workflow, 'Step 1');
        $step2 = Step::create($workflow, 'Step 2', $step1);
        $step3 = Step::create($workflow, 'Step 3', $step2);
        $this->persistEntity($workflow);

        $this->execute(new Input((string) $workflow->id, (string) $step3->id));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $persistedWorkflow = $this->findEntity(Workflow::class, ['id' => $workflow->id], true);

        self::assertCount(2, $persistedWorkflow->steps);

        $stepsOrdered = $persistedWorkflow->steps->toArray();
        self::assertSame('Step 1', $stepsOrdered[0]->content);
        self::assertSame(1, $stepsOrdered[0]->order);
        self::assertSame('Step 2', $stepsOrdered[1]->content);
        self::assertSame(2, $stepsOrdered[1]->order);
    }

    public function testItShouldThrowExceptionWhenWorkflowNotFound(): void
    {
        $this->expectException(WorkflowNotFoundException::class);

        $this->execute(new Input('non-existent-workflow', '00000000-0000-0000-0000-000000000000'));
    }

    public function testItShouldThrowExceptionWhenStepNotFound(): void
    {
        $workflow = self::draftWorkflowEntity();
        $this->persistEntity($workflow);

        $nonExistentId = '00000000-0000-0000-0000-000000000000';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(\sprintf('Step with ID "%s" not found.', $nonExistentId));

        $this->execute(new Input((string) $workflow->id, $nonExistentId));
    }

    public function testItShouldThrowExceptionWhenWorkflowIsArchived(): void
    {
        $workflow = self::draftWorkflowEntity();
        $step = Step::create($workflow, 'Step content');
        $workflow->archive();
        $this->persistEntity($workflow);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot perform this action on an archived directive.');

        $this->execute(new Input((string) $workflow->id, (string) $step->id));
    }
}
