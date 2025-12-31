<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\Authoring\UserInterface\Http\Api\Workflow;

use Cake\Chronos\Chronos;
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
    public function testItShouldMoveStepToFirstPosition(): void
    {
        $workflow = self::draftWorkflowEntity();
        $step1 = Step::create($workflow, 'Step 1');
        $step2 = Step::create($workflow, 'Step 2', $step1);
        $step3 = Step::create($workflow, 'Step 3', $step2);
        $this->persistEntity($workflow);

        $this->moveStep((string) $workflow->id, $step3->id->toString(), ['position' => 1]);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        self::assertResponseReturnsJson([
            'id'          => (string) $workflow->id,
            'createdAt'   => Chronos::now()->toIso8601String(),
            'updatedAt'   => Chronos::now()->toIso8601String(),
            'state'       => 'draft',
            'name'        => $workflow->name,
            'description' => $workflow->description,
            'content'     => null,
            'examples'    => [],
            'steps'       => [
                [
                    'id'        => $step3->id->toString(),
                    'createdAt' => Chronos::now()->toIso8601String(),
                    'updatedAt' => Chronos::now()->toIso8601String(),
                    'order'     => 1,
                    'content'   => 'Step 3',
                ],
                [
                    'id'        => $step1->id->toString(),
                    'createdAt' => Chronos::now()->toIso8601String(),
                    'updatedAt' => Chronos::now()->toIso8601String(),
                    'order'     => 2,
                    'content'   => 'Step 1',
                ],
                [
                    'id'        => $step2->id->toString(),
                    'createdAt' => Chronos::now()->toIso8601String(),
                    'updatedAt' => Chronos::now()->toIso8601String(),
                    'order'     => 3,
                    'content'   => 'Step 2',
                ],
            ],
        ]);

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);
    }

    public function testItShouldMoveStepToMiddlePosition(): void
    {
        $workflow = self::draftWorkflowEntity();
        $step1 = Step::create($workflow, 'Step 1');
        $step2 = Step::create($workflow, 'Step 2', $step1);
        $step3 = Step::create($workflow, 'Step 3', $step2);
        $this->persistEntity($workflow);

        $this->moveStep((string) $workflow->id, $step3->id->toString(), ['position' => 2]);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        self::assertResponseReturnsJson([
            'id'          => (string) $workflow->id,
            'createdAt'   => Chronos::now()->toIso8601String(),
            'updatedAt'   => Chronos::now()->toIso8601String(),
            'state'       => 'draft',
            'name'        => $workflow->name,
            'description' => $workflow->description,
            'content'     => null,
            'examples'    => [],
            'steps'       => [
                [
                    'id'        => $step1->id->toString(),
                    'createdAt' => Chronos::now()->toIso8601String(),
                    'updatedAt' => Chronos::now()->toIso8601String(),
                    'order'     => 1,
                    'content'   => 'Step 1',
                ],
                [
                    'id'        => $step3->id->toString(),
                    'createdAt' => Chronos::now()->toIso8601String(),
                    'updatedAt' => Chronos::now()->toIso8601String(),
                    'order'     => 2,
                    'content'   => 'Step 3',
                ],
                [
                    'id'        => $step2->id->toString(),
                    'createdAt' => Chronos::now()->toIso8601String(),
                    'updatedAt' => Chronos::now()->toIso8601String(),
                    'order'     => 3,
                    'content'   => 'Step 2',
                ],
            ],
        ]);

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);
    }

    public function testItShouldMoveStepToLastPosition(): void
    {
        $workflow = self::draftWorkflowEntity();
        $step1 = Step::create($workflow, 'Step 1');
        $step2 = Step::create($workflow, 'Step 2', $step1);
        $step3 = Step::create($workflow, 'Step 3', $step2);
        $this->persistEntity($workflow);

        $this->moveStep((string) $workflow->id, $step1->id->toString(), ['position' => 3]);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        self::assertResponseReturnsJson([
            'id'          => (string) $workflow->id,
            'createdAt'   => Chronos::now()->toIso8601String(),
            'updatedAt'   => Chronos::now()->toIso8601String(),
            'state'       => 'draft',
            'name'        => $workflow->name,
            'description' => $workflow->description,
            'content'     => null,
            'examples'    => [],
            'steps'       => [
                [
                    'id'        => $step2->id->toString(),
                    'createdAt' => Chronos::now()->toIso8601String(),
                    'updatedAt' => Chronos::now()->toIso8601String(),
                    'order'     => 1,
                    'content'   => 'Step 2',
                ],
                [
                    'id'        => $step3->id->toString(),
                    'createdAt' => Chronos::now()->toIso8601String(),
                    'updatedAt' => Chronos::now()->toIso8601String(),
                    'order'     => 2,
                    'content'   => 'Step 3',
                ],
                [
                    'id'        => $step1->id->toString(),
                    'createdAt' => Chronos::now()->toIso8601String(),
                    'updatedAt' => Chronos::now()->toIso8601String(),
                    'order'     => 3,
                    'content'   => 'Step 1',
                ],
            ],
        ]);

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);
    }

    public function testItShouldReturn404WhenWorkflowNotFound(): void
    {
        $this->moveStep('non-existent-workflow', '00000000-0000-0000-0000-000000000000', ['position' => 1]);

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testItShouldReturn400WhenStepNotFound(): void
    {
        $workflow = self::draftWorkflowEntity();
        Step::create($workflow, 'Step 1');
        $this->persistEntity($workflow);

        $this->moveStep((string) $workflow->id, '00000000-0000-0000-0000-000000000000', ['position' => 1]);

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testItShouldReturn400WhenPositionIsInvalid(): void
    {
        $workflow = self::draftWorkflowEntity();
        $step = Step::create($workflow, 'Step 1');
        $this->persistEntity($workflow);

        $this->moveStep((string) $workflow->id, $step->id->toString(), ['position' => 5]);

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testItShouldReturn422WhenPositionIsZero(): void
    {
        $workflow = self::draftWorkflowEntity();
        $step = Step::create($workflow, 'Step 1');
        $this->persistEntity($workflow);

        $this->moveStep((string) $workflow->id, $step->id->toString(), ['position' => 0]);

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testItShouldReturn422WhenPositionIsNegative(): void
    {
        $workflow = self::draftWorkflowEntity();
        $step = Step::create($workflow, 'Step 1');
        $this->persistEntity($workflow);

        $this->moveStep((string) $workflow->id, $step->id->toString(), ['position' => -1]);

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testItShouldReturn422WhenPositionIsMissing(): void
    {
        $workflow = self::draftWorkflowEntity();
        $step = Step::create($workflow, 'Step 1');
        $this->persistEntity($workflow);

        $this->moveStep((string) $workflow->id, $step->id->toString(), []);

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testItShouldReturn400WhenWorkflowIsArchived(): void
    {
        $workflow = self::draftWorkflowEntity();
        $step1 = Step::create($workflow, 'Step 1');
        Step::create($workflow, 'Step 2', $step1);
        $workflow->archive();
        $this->persistEntity($workflow);

        $this->moveStep((string) $workflow->id, $step1->id->toString(), ['position' => 2]);

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function moveStep(string $workflowId, string $stepId, array $payload): void
    {
        DomainEventQueue::reset();
        $this->postJson(\sprintf('/api/authoring/workflows/%s/steps/%s/move', $workflowId, $stepId), $payload);
    }
}
