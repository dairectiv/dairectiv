<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\Authoring\UserInterface\Http\Api\Workflow;

use Dairectiv\Authoring\Domain\Object\Directive\Event\DirectiveUpdated;
use Dairectiv\SharedKernel\Domain\Object\Event\DomainEventQueue;
use Dairectiv\Tests\Framework\IntegrationTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Response;

#[Group('integration')]
#[Group('authoring')]
#[Group('api')]
final class AddWorkflowExampleTest extends IntegrationTestCase
{
    public function testItShouldAddExampleToWorkflow(): void
    {
        $workflow = self::draftWorkflowEntity();
        $this->persistEntity($workflow);

        $this->addExample((string) $workflow->id, [
            'scenario'    => 'User wants to create a new account',
            'input'       => 'User provides email and password',
            'output'      => 'Account is created and confirmation email is sent',
            'explanation' => 'This demonstrates the happy path for account creation',
        ]);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);
        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $response = $this->client->getResponse();
        self::assertTrue($response->headers->has('Location'));
        $location = $response->headers->get('Location');
        self::assertNotNull($location);
        self::assertStringContainsString(\sprintf('/api/authoring/workflows/%s/examples/', $workflow->id), $location);
    }

    public function testItShouldAddExampleWithoutExplanation(): void
    {
        $workflow = self::draftWorkflowEntity();
        $this->persistEntity($workflow);

        $this->addExample((string) $workflow->id, [
            'scenario' => 'User wants to create a new account',
            'input'    => 'User provides email and password',
            'output'   => 'Account is created and confirmation email is sent',
        ]);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);
        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);
    }

    public function testItShouldReturn404WhenWorkflowNotFound(): void
    {
        $this->addExample('non-existent-workflow', [
            'scenario' => 'User wants to create a new account',
            'input'    => 'User provides email and password',
            'output'   => 'Account is created and confirmation email is sent',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testItShouldReturn400WhenWorkflowIsArchived(): void
    {
        $workflow = self::draftWorkflowEntity();
        $workflow->archive();
        $this->persistEntity($workflow);

        $this->addExample((string) $workflow->id, [
            'scenario' => 'User wants to create a new account',
            'input'    => 'User provides email and password',
            'output'   => 'Account is created and confirmation email is sent',
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
                ['propertyPath' => 'scenario', 'title' => 'This value should be of type string.'],
                ['propertyPath' => 'input', 'title' => 'This value should be of type string.'],
                ['propertyPath' => 'output', 'title' => 'This value should be of type string.'],
            ],
        ];

        yield 'blank scenario' => [
            'payload'            => ['scenario' => '', 'input' => 'input', 'output' => 'output'],
            'expectedViolations' => [
                ['propertyPath' => 'scenario', 'title' => 'This value should not be blank.'],
            ],
        ];

        yield 'blank input' => [
            'payload'            => ['scenario' => 'scenario', 'input' => '', 'output' => 'output'],
            'expectedViolations' => [
                ['propertyPath' => 'input', 'title' => 'This value should not be blank.'],
            ],
        ];

        yield 'blank output' => [
            'payload'            => ['scenario' => 'scenario', 'input' => 'input', 'output' => ''],
            'expectedViolations' => [
                ['propertyPath' => 'output', 'title' => 'This value should not be blank.'],
            ],
        ];

        yield 'missing scenario' => [
            'payload'            => ['input' => 'input', 'output' => 'output'],
            'expectedViolations' => [
                ['propertyPath' => 'scenario', 'title' => 'This value should be of type string.'],
            ],
        ];

        yield 'missing input' => [
            'payload'            => ['scenario' => 'scenario', 'output' => 'output'],
            'expectedViolations' => [
                ['propertyPath' => 'input', 'title' => 'This value should be of type string.'],
            ],
        ];

        yield 'missing output' => [
            'payload'            => ['scenario' => 'scenario', 'input' => 'input'],
            'expectedViolations' => [
                ['propertyPath' => 'output', 'title' => 'This value should be of type string.'],
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
        $this->postJson(\sprintf('/api/authoring/workflows/%s/examples', $workflow->id), $payload);

        self::assertUnprocessableResponse($expectedViolations);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function addExample(string $workflowId, array $payload): void
    {
        DomainEventQueue::reset();
        $this->postJson(\sprintf('/api/authoring/workflows/%s/examples', $workflowId), $payload);
    }
}
