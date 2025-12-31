<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\Authoring\UserInterface\Http\Api\Rule;

use Cake\Chronos\Chronos;
use Dairectiv\Authoring\Domain\Object\Directive\Event\DirectiveDrafted;
use Dairectiv\Tests\Framework\IntegrationTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Response;

#[Group('integration')]
#[Group('authoring')]
#[Group('api')]
final class DraftRuleTest extends IntegrationTestCase
{
    public function testItShouldDraftRule(): void
    {
        $this->draftRule();

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);

        self::assertResponseReturnsJson([
            'id'          => 'my-rule',
            'name'        => 'My Rule',
            'description' => 'Description',
            'examples'    => [],
            'content'     => null,
            'state'       => 'draft',
            'updatedAt'   => Chronos::now()->toIso8601String(),
            'createdAt'   => Chronos::now()->toIso8601String(),
        ]);

        self::assertDomainEventHasBeenDispatched(DirectiveDrafted::class);
    }

    /**
     * @return iterable<string, array{payload: array<string, mixed>, expectedViolations: list<array{propertyPath: string, title: string}>}>
     */
    public static function provideInvalidPayloads(): iterable
    {
        yield 'empty payload' => [
            'payload'            => [],
            'expectedViolations' => [
                ['propertyPath' => 'name', 'title' => 'This value should be of type string.'],
                ['propertyPath' => 'description', 'title' => 'This value should be of type string.'],
            ],
        ];

        yield 'blank name' => [
            'payload'            => ['name' => '', 'description' => 'Description'],
            'expectedViolations' => [
                ['propertyPath' => 'name', 'title' => 'This value should not be blank.'],
            ],
        ];

        yield 'blank description' => [
            'payload'            => ['name' => 'My Rule', 'description' => ''],
            'expectedViolations' => [
                ['propertyPath' => 'description', 'title' => 'This value should not be blank.'],
            ],
        ];

        yield 'name too long' => [
            'payload'            => ['name' => str_repeat('a', 256), 'description' => 'Description'],
            'expectedViolations' => [
                ['propertyPath' => 'name', 'title' => 'This value is too long. It should have 255 characters or less.'],
            ],
        ];

        yield 'description too long' => [
            'payload'            => ['name' => 'My Rule', 'description' => str_repeat('a', 501)],
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
        $this->postJson('/api/authoring/rules', $payload);

        self::assertUnprocessableResponse($expectedViolations);
    }

    public function testItShouldBeInConflitDueToRuleAlreadyExists(): void
    {
        $rule = self::draftRuleEntity('my-rule');
        $this->persistEntity($rule);

        $this->draftRule();

        self::assertResponseStatusCodeSame(Response::HTTP_CONFLICT);
    }

    /**
     * @param array<string, mixed>|null $payload
     */
    private function draftRule(
        string $name = 'My Rule',
        string $description = 'Description',
        ?array $payload = null,
    ): void {
        $this->postJson('/api/authoring/rules', $payload ?? [
            'name'        => $name,
            'description' => $description,
        ]);
    }
}
