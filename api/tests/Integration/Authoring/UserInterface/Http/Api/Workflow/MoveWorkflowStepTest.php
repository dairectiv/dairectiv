<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\Authoring\UserInterface\Http\Api\Workflow;

use Dairectiv\Authoring\Domain\Object\Directive\Event\DirectiveUpdated;
use Dairectiv\Authoring\Domain\Object\Workflow\Step\Step;
use Dairectiv\SharedKernel\Domain\Object\Event\DomainEventQueue;
use Dairectiv\Tests\Framework\IntegrationTestCase;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Response;

#[Group('integration')]
#[Group('authoring')]
#[Group('api')]
final class MoveWorkflowStepTest extends IntegrationTestCase
{
    public function testItShouldMoveStepToFirstPositionWithNullAfterStepId(): void
    {
        $workflow = self::draftWorkflowEntity();
        $step1 = Step::create($workflow, 'Step 1');
        $step2 = Step::create($workflow, 'Step 2', $step1);
        $step3 = Step::create($workflow, 'Step 3', $step2);
        $this->persistEntity($workflow);

        $this->moveStep((string) $workflow->id, $step3->id->toString(), ['afterStepId' => null]);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);
    }

    public function testItShouldMoveStepAfterSpecificStep(): void
    {
        $workflow = self::draftWorkflowEntity();
        $step1 = Step::create($workflow, 'Step 1');
        $step2 = Step::create($workflow, 'Step 2', $step1);
        $step3 = Step::create($workflow, 'Step 3', $step2);
        $this->persistEntity($workflow);

        // Move step3 after step1 (resulting order: step1, step3, step2)
        $this->moveStep((string) $workflow->id, $step3->id->toString(), ['afterStepId' => $step1->id->toString()]);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);
    }

    public function testItShouldMoveStepToLastPosition(): void
    {
        $workflow = self::draftWorkflowEntity();
        $step1 = Step::create($workflow, 'Step 1');
        $step2 = Step::create($workflow, 'Step 2', $step1);
        $step3 = Step::create($workflow, 'Step 3', $step2);
        $this->persistEntity($workflow);

        // Move step1 after step3 (resulting order: step2, step3, step1)
        $this->moveStep((string) $workflow->id, $step1->id->toString(), ['afterStepId' => $step3->id->toString()]);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);
    }

    public function testItShouldMoveStepWithEmptyPayloadToFirstPosition(): void
    {
        $workflow = self::draftWorkflowEntity();
        $step1 = Step::create($workflow, 'Step 1');
        $step2 = Step::create($workflow, 'Step 2', $step1);
        $this->persistEntity($workflow);

        // Empty payload means afterStepId is null, which places step at first position
        $this->moveStep((string) $workflow->id, $step2->id->toString(), []);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);
    }

    public function testItShouldReturn404WhenWorkflowNotFound(): void
    {
        // Use valid UUID v7 format for step ID (not a nil UUID)
        $this->moveStep('non-existent-workflow', '019b7460-798e-77f8-af01-bc6ab6e25f84', ['afterStepId' => null]);

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testItShouldReturn400WhenStepNotFound(): void
    {
        $workflow = self::draftWorkflowEntity();
        Step::create($workflow, 'Step 1');
        $this->persistEntity($workflow);

        // Use valid UUID v7 format that doesn't exist in the workflow
        $this->moveStep((string) $workflow->id, '019b7460-798e-77f8-af01-bc6ab6e25f84', ['afterStepId' => null]);

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testItShouldReturn400WhenAfterStepIdNotFound(): void
    {
        $workflow = self::draftWorkflowEntity();
        $step = Step::create($workflow, 'Step 1');
        $this->persistEntity($workflow);

        // Use a valid UUID v7 format that doesn't exist in the workflow
        $this->moveStep((string) $workflow->id, $step->id->toString(), ['afterStepId' => '019b7460-798e-77f8-af01-bc6ab6e25f84']);

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testItShouldReturn422WhenAfterStepIdIsInvalidUuid(): void
    {
        $workflow = self::draftWorkflowEntity();
        $step = Step::create($workflow, 'Step 1');
        $this->persistEntity($workflow);

        $this->moveStep((string) $workflow->id, $step->id->toString(), ['afterStepId' => 'not-a-uuid']);

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testItShouldReturn400WhenWorkflowIsArchived(): void
    {
        $workflow = self::draftWorkflowEntity();
        $step1 = Step::create($workflow, 'Step 1');
        Step::create($workflow, 'Step 2', $step1);
        $workflow->archive();
        $this->persistEntity($workflow);

        $this->moveStep((string) $workflow->id, $step1->id->toString(), ['afterStepId' => null]);

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function moveStep(string $workflowId, string $stepId, array $payload): void
    {
        DomainEventQueue::reset();
        $this->putJson(\sprintf('/api/authoring/workflows/%s/steps/%s/move', $workflowId, $stepId), $payload);
    }
}
