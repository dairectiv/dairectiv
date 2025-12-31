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
final class UpdateWorkflowStepTest extends IntegrationTestCase
{
    public function testItShouldUpdateStepContent(): void
    {
        $workflow = self::draftWorkflowEntity();
        $step = Step::create($workflow, 'Original content');
        $this->persistEntity($workflow);

        $this->updateStep((string) $workflow->id, $step->id->toString(), [
            'content' => 'Updated content',
        ]);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        self::assertResponseReturnsJson([
            'id'        => $step->id->toString(),
            'createdAt' => Chronos::now()->toIso8601String(),
            'updatedAt' => Chronos::now()->toIso8601String(),
            'order'     => 1,
            'content'   => 'Updated content',
        ]);

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);
    }

    public function testItShouldReturn404WhenWorkflowNotFound(): void
    {
        $this->updateStep('non-existent-workflow', '00000000-0000-0000-0000-000000000000', [
            'content' => 'Updated content',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testItShouldReturn400WhenStepNotFound(): void
    {
        $workflow = self::draftWorkflowEntity();
        $this->persistEntity($workflow);

        $this->updateStep((string) $workflow->id, '00000000-0000-0000-0000-000000000000', [
            'content' => 'Updated content',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testItShouldReturn400WhenNoFieldsProvided(): void
    {
        $workflow = self::draftWorkflowEntity();
        $step = Step::create($workflow, 'Original content');
        $this->persistEntity($workflow);

        $this->updateStep((string) $workflow->id, $step->id->toString(), []);

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testItShouldReturn400WhenWorkflowIsArchived(): void
    {
        $workflow = self::draftWorkflowEntity();
        $step = Step::create($workflow, 'Original content');
        $workflow->archive();
        $this->persistEntity($workflow);

        $this->updateStep((string) $workflow->id, $step->id->toString(), [
            'content' => 'Updated content',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function updateStep(string $workflowId, string $stepId, array $payload): void
    {
        DomainEventQueue::reset();
        $this->patchJson(\sprintf('/api/authoring/workflows/%s/steps/%s', $workflowId, $stepId), $payload);
    }
}
