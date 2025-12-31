<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\Authoring\UserInterface\Http\Api\Rule;

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
final class UpdateRuleTest extends IntegrationTestCase
{
    public function testItShouldUpdateRuleName(): void
    {
        $rule = self::draftRuleEntity(name: 'Original Name', description: 'Original description');
        $this->persistEntity($rule);

        $this->updateRule((string) $rule->id, ['name' => 'Updated Name']);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        self::assertResponseReturnsJson([
            'id'          => (string) $rule->id,
            'name'        => 'Updated Name',
            'description' => 'Original description',
            'examples'    => [],
            'content'     => null,
            'state'       => 'draft',
            'updatedAt'   => Chronos::now()->toIso8601String(),
            'createdAt'   => Chronos::now()->toIso8601String(),
        ]);

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);
    }

    public function testItShouldUpdateRuleDescription(): void
    {
        $rule = self::draftRuleEntity(name: 'My Rule', description: 'Original description');
        $this->persistEntity($rule);

        $this->updateRule((string) $rule->id, ['description' => 'Updated description']);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        self::assertResponseReturnsJson([
            'id'          => (string) $rule->id,
            'name'        => 'My Rule',
            'description' => 'Updated description',
            'examples'    => [],
            'content'     => null,
            'state'       => 'draft',
            'updatedAt'   => Chronos::now()->toIso8601String(),
            'createdAt'   => Chronos::now()->toIso8601String(),
        ]);

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);
    }

    public function testItShouldUpdateRuleContent(): void
    {
        $rule = self::draftRuleEntity();
        $this->persistEntity($rule);

        $this->updateRule((string) $rule->id, ['content' => 'New content']);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        self::assertResponseReturnsJson([
            'id'          => (string) $rule->id,
            'name'        => $rule->name,
            'description' => $rule->description,
            'examples'    => [],
            'content'     => 'New content',
            'state'       => 'draft',
            'updatedAt'   => Chronos::now()->toIso8601String(),
            'createdAt'   => Chronos::now()->toIso8601String(),
        ]);

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);
    }

    public function testItShouldUpdateAllFields(): void
    {
        $rule = self::draftRuleEntity();
        $this->persistEntity($rule);

        $this->updateRule((string) $rule->id, [
            'name'        => 'New Name',
            'description' => 'New description',
            'content'     => 'New content',
        ]);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        self::assertResponseReturnsJson([
            'id'          => (string) $rule->id,
            'name'        => 'New Name',
            'description' => 'New description',
            'examples'    => [],
            'content'     => 'New content',
            'state'       => 'draft',
            'updatedAt'   => Chronos::now()->toIso8601String(),
            'createdAt'   => Chronos::now()->toIso8601String(),
        ]);

        // Two events: one from metadata update, one from content update
        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class, 2);
    }

    public function testItShouldReturn404WhenRuleNotFound(): void
    {
        $this->updateRule('non-existent-rule', ['name' => 'Updated Name']);

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testItShouldReturn400WhenNoFieldsProvided(): void
    {
        $rule = self::draftRuleEntity();
        $this->persistEntity($rule);

        $this->updateRule((string) $rule->id, []);

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testItShouldReturn400WhenRuleIsArchived(): void
    {
        $rule = self::draftRuleEntity();
        $rule->archive();
        $this->persistEntity($rule);

        $this->updateRule((string) $rule->id, ['name' => 'Updated Name']);

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
        $rule = self::draftRuleEntity();
        $this->persistEntity($rule);

        DomainEventQueue::reset();
        $this->patchJson(\sprintf('/api/authoring/rules/%s', $rule->id), $payload);

        self::assertUnprocessableResponse($expectedViolations);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function updateRule(string $id, array $payload): void
    {
        DomainEventQueue::reset();
        $this->patchJson(\sprintf('/api/authoring/rules/%s', $id), $payload);
    }
}
