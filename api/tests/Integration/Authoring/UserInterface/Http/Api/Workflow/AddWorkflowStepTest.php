<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\Authoring\UserInterface\Http\Api\Workflow;

use Dairectiv\Authoring\Domain\Object\Directive\Event\DirectiveUpdated;
use Dairectiv\Authoring\Domain\Object\Workflow\Step\Step;
use Dairectiv\SharedKernel\Domain\Object\Event\DomainEventQueue;
use Dairectiv\Tests\Framework\IntegrationTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Response;

#[Group('integration')]
#[Group('authoring')]
#[Group('api')]
final class AddWorkflowStepTest extends IntegrationTestCase
{
    public function testItShouldAddStepToWorkflow(): void
    {
        $workflow = self::draftWorkflowEntity();
        $this->persistEntity($workflow);

        $this->addStep((string) $workflow->id, [
            'content' => 'First, analyze the requirements',
        ]);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);
        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $response = $this->client->getResponse();
        self::assertTrue($response->headers->has('Location'));
        $location = $response->headers->get('Location');
        self::assertNotNull($location);
        self::assertStringContainsString(\sprintf('/api/authoring/workflows/%s/steps/', $workflow->id), $location);
    }

    public function testItShouldAddStepAfterSpecificStep(): void
    {
        $workflow = self::draftWorkflowEntity();
        $step1 = Step::create($workflow, 'Step 1');
        Step::create($workflow, 'Step 2', $step1);
        $this->persistEntity($workflow);

        $this->addStep((string) $workflow->id, [
            'content'     => 'Inserted Step',
            'afterStepId' => (string) $step1->id,
        ]);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);
        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);
    }

    public function testItShouldReturn404WhenWorkflowNotFound(): void
    {
        $this->addStep('non-existent-workflow', [
            'content' => 'Step content',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testItShouldReturn400WhenWorkflowIsArchived(): void
    {
        $workflow = self::draftWorkflowEntity();
        $workflow->archive();
        $this->persistEntity($workflow);

        $this->addStep((string) $workflow->id, [
            'content' => 'Step content',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testItShouldReturn400WhenAfterStepNotFound(): void
    {
        $workflow = self::draftWorkflowEntity();
        $this->persistEntity($workflow);

        $this->addStep((string) $workflow->id, [
            'content'     => 'Step content',
            'afterStepId' => '00000000-0000-0000-0000-000000000000',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    /**
     * @return iterable<string, array{payload: array<string, mixed>, expectedViolations: list<array{propertyPath: string, title: string}>}>
     */
    public static function provideInvalidPayloads(): iterable
    {
        yield 'empty payload' => [
            'payload'            => [],
            'expectedViolations' => [
                ['propertyPath' => 'content', 'title' => 'This value should be of type string.'],
            ],
        ];

        yield 'blank content' => [
            'payload'            => ['content' => ''],
            'expectedViolations' => [
                ['propertyPath' => 'content', 'title' => 'This value should not be blank.'],
            ],
        ];

        yield 'missing content' => [
            'payload'            => ['afterStepId' => '00000000-0000-0000-0000-000000000000'],
            'expectedViolations' => [
                ['propertyPath' => 'content', 'title' => 'This value should be of type string.'],
            ],
        ];
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<array{propertyPath: string, title: string}> $expectedViolations
     */
    #[DataProvider('provideInvalidPayloads')]
    public function testItShouldBeUnprocessable(array $payload, array $expectedViolations): void
    {
        $workflow = self::draftWorkflowEntity();
        $this->persistEntity($workflow);

        DomainEventQueue::reset();
        $this->postJson(\sprintf('/api/authoring/workflows/%s/steps', $workflow->id), $payload);

        self::assertUnprocessableResponse($expectedViolations);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function addStep(string $workflowId, array $payload): void
    {
        DomainEventQueue::reset();
        $this->postJson(\sprintf('/api/authoring/workflows/%s/steps', $workflowId), $payload);
    }
}
