<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\Authoring\UserInterface\Http\Api\Workflow;

use Dairectiv\Authoring\Domain\Object\Directive\Event\DirectiveUpdated;
use Dairectiv\Authoring\Domain\Object\Workflow\Step\Step;
use Dairectiv\Authoring\Domain\Object\Workflow\Workflow;
use Dairectiv\SharedKernel\Domain\Object\Event\DomainEventQueue;
use Dairectiv\Tests\Framework\IntegrationTestCase;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Response;

#[Group('integration')]
#[Group('authoring')]
#[Group('api')]
final class RemoveWorkflowStepTest extends IntegrationTestCase
{
    public function testItShouldRemoveStep(): void
    {
        $workflow = self::draftWorkflowEntity();
        $step = Step::create($workflow, 'Step content');
        $this->persistEntity($workflow);

        self::assertCount(1, $workflow->steps);

        $this->removeStep((string) $workflow->id, $step->id->toString());

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

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

        $this->removeStep((string) $workflow->id, $step2->id->toString());

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $persistedWorkflow = $this->findEntity(Workflow::class, ['id' => $workflow->id], true);

        self::assertCount(2, $persistedWorkflow->steps);

        $remainingContents = $persistedWorkflow->steps->map(static fn ($s) => $s->content)->toArray();
        self::assertContains('Step 1', $remainingContents);
        self::assertContains('Step 3', $remainingContents);
        self::assertNotContains('Step 2', $remainingContents);
    }

    public function testItShouldReturn404WhenWorkflowNotFound(): void
    {
        $this->removeStep('non-existent-workflow', '00000000-0000-0000-0000-000000000000');

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testItShouldReturn404WhenStepNotFound(): void
    {
        $workflow = self::draftWorkflowEntity();
        $this->persistEntity($workflow);

        $this->removeStep((string) $workflow->id, '00000000-0000-0000-0000-000000000000');

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testItShouldReturn404WhenWorkflowIsArchived(): void
    {
        $workflow = self::draftWorkflowEntity();
        $step = Step::create($workflow, 'Step content');
        $workflow->archive();
        $this->persistEntity($workflow);

        $this->removeStep((string) $workflow->id, $step->id->toString());

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    private function removeStep(string $workflowId, string $stepId): void
    {
        DomainEventQueue::reset();
        $this->deleteJson(\sprintf('/api/authoring/workflows/%s/steps/%s', $workflowId, $stepId));
    }
}
