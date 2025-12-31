<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\Authoring\UserInterface\Http\Api\Workflow;

use Cake\Chronos\Chronos;
use Dairectiv\Authoring\Domain\Object\Directive\Event\DirectiveUpdated;
use Dairectiv\SharedKernel\Domain\Object\Event\DomainEventQueue;
use Dairectiv\Tests\Framework\IntegrationTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Response;

#[Group('integration')]
#[Group('authoring')]
#[Group('api')]
final class UpdateWorkflowTest extends IntegrationTestCase
{
    public function testItShouldUpdateWorkflowName(): void
    {
        $workflow = self::draftWorkflowEntity(name: 'Original Name', description: 'Original description');
        $this->persistEntity($workflow);

        $this->updateWorkflow((string) $workflow->id, ['name' => 'Updated Name']);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        self::assertResponseReturnsJson([
            'id'          => (string) $workflow->id,
            'name'        => 'Updated Name',
            'description' => 'Original description',
            'examples'    => [],
            'steps'       => [],
            'content'     => null,
            'state'       => 'draft',
            'updatedAt'   => Chronos::now()->toIso8601String(),
            'createdAt'   => Chronos::now()->toIso8601String(),
        ]);

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);
    }

    public function testItShouldUpdateWorkflowDescription(): void
    {
        $workflow = self::draftWorkflowEntity(name: 'My Workflow', description: 'Original description');
        $this->persistEntity($workflow);

        $this->updateWorkflow((string) $workflow->id, ['description' => 'Updated description']);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        self::assertResponseReturnsJson([
            'id'          => (string) $workflow->id,
            'name'        => 'My Workflow',
            'description' => 'Updated description',
            'examples'    => [],
            'steps'       => [],
            'content'     => null,
            'state'       => 'draft',
            'updatedAt'   => Chronos::now()->toIso8601String(),
            'createdAt'   => Chronos::now()->toIso8601String(),
        ]);

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);
    }

    public function testItShouldUpdateWorkflowContent(): void
    {
        $workflow = self::draftWorkflowEntity();
        $this->persistEntity($workflow);

        $this->updateWorkflow((string) $workflow->id, ['content' => 'New content']);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        self::assertResponseReturnsJson([
            'id'          => (string) $workflow->id,
            'name'        => $workflow->name,
            'description' => $workflow->description,
            'examples'    => [],
            'steps'       => [],
            'content'     => 'New content',
            'state'       => 'draft',
            'updatedAt'   => Chronos::now()->toIso8601String(),
            'createdAt'   => Chronos::now()->toIso8601String(),
        ]);

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);
    }

    public function testItShouldUpdateAllFields(): void
    {
        $workflow = self::draftWorkflowEntity();
        $this->persistEntity($workflow);

        $this->updateWorkflow((string) $workflow->id, [
            'name'        => 'New Name',
            'description' => 'New description',
            'content'     => 'New content',
        ]);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        self::assertResponseReturnsJson([
            'id'          => (string) $workflow->id,
            'name'        => 'New Name',
            'description' => 'New description',
            'examples'    => [],
            'steps'       => [],
            'content'     => 'New content',
            'state'       => 'draft',
            'updatedAt'   => Chronos::now()->toIso8601String(),
            'createdAt'   => Chronos::now()->toIso8601String(),
        ]);

        // Two events: one from metadata update, one from content update
        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class, 2);
    }

    public function testItShouldReturn404WhenWorkflowNotFound(): void
    {
        $this->updateWorkflow('non-existent-workflow', ['name' => 'Updated Name']);

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testItShouldReturn400WhenNoFieldsProvided(): void
    {
        $workflow = self::draftWorkflowEntity();
        $this->persistEntity($workflow);

        $this->updateWorkflow((string) $workflow->id, []);

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testItShouldReturn400WhenWorkflowIsArchived(): void
    {
        $workflow = self::draftWorkflowEntity();
        $workflow->archive();
        $this->persistEntity($workflow);

        $this->updateWorkflow((string) $workflow->id, ['name' => 'Updated Name']);

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    /**
     * @return iterable<string, array{payload: array<string, mixed>, expectedViolations: list<array{propertyPath: string, title: string}>}>
     */
    public static function provideInvalidPayloads(): iterable
    {
        yield 'name too long' => [
            'payload'            => ['name' => str_repeat('a', 256)],
            'expectedViolations' => [
                ['propertyPath' => 'name', 'title' => 'This value is too long. It should have 255 characters or less.'],
            ],
        ];

        yield 'description too long' => [
            'payload'            => ['description' => str_repeat('a', 501)],
            'expectedViolations' => [
                ['propertyPath' => 'description', 'title' => 'This value is too long. It should have 500 characters or less.'],
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
        $this->patchJson(\sprintf('/api/authoring/workflows/%s', $workflow->id), $payload);

        self::assertUnprocessableResponse($expectedViolations);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function updateWorkflow(string $id, array $payload): void
    {
        DomainEventQueue::reset();
        $this->patchJson(\sprintf('/api/authoring/workflows/%s', $id), $payload);
    }
}
