<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\Authoring\Application\Workflow;

use Cake\Chronos\Chronos;
use Dairectiv\Authoring\Application\Workflow\UpdateStep\Input;
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
final class UpdateStepTest extends IntegrationTestCase
{
    public function testItShouldUpdateStepContent(): void
    {
        $workflow = self::draftWorkflow();
        $step = Step::create($workflow, 'Original content');
        $this->persistEntity($workflow);

        self::assertSame('Original content', $step->content);

        $this->execute(new Input(
            (string) $workflow->id,
            (string) $step->id,
            'Updated content',
        ));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $persistedWorkflow = $this->findEntity(Workflow::class, ['id' => $workflow->id], true);
        $persistedStep = $persistedWorkflow->steps->first();

        self::assertInstanceOf(Step::class, $persistedStep);

        self::assertSame('Updated content', $persistedStep->content);
    }

    public function testItShouldUpdateStepTimestamp(): void
    {
        $workflow = self::draftWorkflow();
        $step = Step::create($workflow, 'Content');
        $this->persistEntity($workflow);

        Chronos::setTestNow(Chronos::now()->addDays(1));

        $this->execute(new Input(
            (string) $workflow->id,
            (string) $step->id,
            'New content',
        ));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $persistedWorkflow = $this->findEntity(Workflow::class, ['id' => $workflow->id], true);
        $persistedStep = $persistedWorkflow->steps->first();

        self::assertInstanceOf(Step::class, $persistedStep);
        self::assertTrue($persistedStep->updatedAt->greaterThan($persistedStep->createdAt));
    }

    public function testItShouldPreserveStepOrder(): void
    {
        $workflow = self::draftWorkflow();
        $step1 = Step::create($workflow, 'Step 1');
        $step2 = Step::create($workflow, 'Step 2', $step1);
        Step::create($workflow, 'Step 3', $step2);
        $this->persistEntity($workflow);

        $this->execute(new Input(
            (string) $workflow->id,
            (string) $step2->id,
            'Updated Step 2',
        ));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $persistedWorkflow = $this->findEntity(Workflow::class, ['id' => $workflow->id], true);

        $stepsOrdered = $persistedWorkflow->steps->toArray();
        self::assertSame('Step 1', $stepsOrdered[0]->content);
        self::assertSame(1, $stepsOrdered[0]->order);
        self::assertSame('Updated Step 2', $stepsOrdered[1]->content);
        self::assertSame(2, $stepsOrdered[1]->order);
        self::assertSame('Step 3', $stepsOrdered[2]->content);
        self::assertSame(3, $stepsOrdered[2]->order);
    }

    public function testItShouldThrowExceptionWhenWorkflowNotFound(): void
    {
        $this->expectException(WorkflowNotFoundException::class);

        $this->execute(new Input(
            'non-existent-workflow',
            '00000000-0000-0000-0000-000000000000',
            'Content',
        ));
    }

    public function testItShouldThrowExceptionWhenStepNotFound(): void
    {
        $workflow = self::draftWorkflow();
        $this->persistEntity($workflow);

        $nonExistentId = '00000000-0000-0000-0000-000000000000';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(\sprintf('Step with ID "%s" not found.', $nonExistentId));

        $this->execute(new Input((string) $workflow->id, $nonExistentId, 'Content'));
    }

    public function testItShouldThrowExceptionWhenWorkflowIsArchived(): void
    {
        $workflow = self::draftWorkflow();
        $step = Step::create($workflow, 'Content');
        $workflow->archive();
        $this->persistEntity($workflow);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot perform this action on an archived directive.');

        $this->execute(new Input((string) $workflow->id, (string) $step->id, 'New content'));
    }

    public function testItShouldUpdateOnlySpecifiedStep(): void
    {
        $workflow = self::draftWorkflow();
        $step1 = Step::create($workflow, 'Original Step 1');
        Step::create($workflow, 'Original Step 2', $step1);
        $this->persistEntity($workflow);

        $this->execute(new Input(
            (string) $workflow->id,
            (string) $step1->id,
            'Updated Step 1',
        ));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $persistedWorkflow = $this->findEntity(Workflow::class, ['id' => $workflow->id], true);
        $stepsOrdered = $persistedWorkflow->steps->toArray();

        self::assertSame('Updated Step 1', $stepsOrdered[0]->content);
        self::assertSame('Original Step 2', $stepsOrdered[1]->content);
    }
}
