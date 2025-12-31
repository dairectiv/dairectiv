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
final class AddRuleExampleTest extends IntegrationTestCase
{
    public function testItShouldAddExampleToRule(): void
    {
        $rule = self::draftRuleEntity();
        $this->persistEntity($rule);

        $this->addExample((string) $rule->id, [
            'good'        => 'const userAge = 25;',
            'bad'         => 'const x = 25;',
            'explanation' => 'Descriptive names improve code readability',
        ]);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);

        self::assertResponseReturnsJson([
            'id'          => '@uuid@',
            'createdAt'   => Chronos::now()->toIso8601String(),
            'updatedAt'   => Chronos::now()->toIso8601String(),
            'good'        => 'const userAge = 25;',
            'bad'         => 'const x = 25;',
            'explanation' => 'Descriptive names improve code readability',
        ]);

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $response = $this->client->getResponse();
        self::assertTrue($response->headers->has('Location'));
        $location = $response->headers->get('Location');
        self::assertNotNull($location);
        self::assertStringContainsString(\sprintf('/api/authoring/rules/%s/examples/', $rule->id), $location);
    }

    public function testItShouldAddExampleWithoutExplanation(): void
    {
        $rule = self::draftRuleEntity();
        $this->persistEntity($rule);

        $this->addExample((string) $rule->id, [
            'good' => 'const userAge = 25;',
            'bad'  => 'const x = 25;',
        ]);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);

        self::assertResponseReturnsJson([
            'id'          => '@uuid@',
            'createdAt'   => Chronos::now()->toIso8601String(),
            'updatedAt'   => Chronos::now()->toIso8601String(),
            'good'        => 'const userAge = 25;',
            'bad'         => 'const x = 25;',
            'explanation' => null,
        ]);

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);
    }

    public function testItShouldReturn404WhenRuleNotFound(): void
    {
        $this->addExample('non-existent-rule', [
            'good' => 'const userAge = 25;',
            'bad'  => 'const x = 25;',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testItShouldReturn400WhenRuleIsArchived(): void
    {
        $rule = self::draftRuleEntity();
        $rule->archive();
        $this->persistEntity($rule);

        $this->addExample((string) $rule->id, [
            'good' => 'const userAge = 25;',
            'bad'  => 'const x = 25;',
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
                ['propertyPath' => 'good', 'title' => 'This value should be of type string.'],
                ['propertyPath' => 'bad', 'title' => 'This value should be of type string.'],
            ],
        ];

        yield 'blank good' => [
            'payload'            => ['good' => '', 'bad' => 'const x = 25;'],
            'expectedViolations' => [
                ['propertyPath' => 'good', 'title' => 'This value should not be blank.'],
            ],
        ];

        yield 'blank bad' => [
            'payload'            => ['good' => 'const userAge = 25;', 'bad' => ''],
            'expectedViolations' => [
                ['propertyPath' => 'bad', 'title' => 'This value should not be blank.'],
            ],
        ];

        yield 'missing good' => [
            'payload'            => ['bad' => 'const x = 25;'],
            'expectedViolations' => [
                ['propertyPath' => 'good', 'title' => 'This value should be of type string.'],
            ],
        ];

        yield 'missing bad' => [
            'payload'            => ['good' => 'const userAge = 25;'],
            'expectedViolations' => [
                ['propertyPath' => 'bad', 'title' => 'This value should be of type string.'],
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
        $this->postJson(\sprintf('/api/authoring/rules/%s/examples', $rule->id), $payload);

        self::assertUnprocessableResponse($expectedViolations);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function addExample(string $ruleId, array $payload): void
    {
        DomainEventQueue::reset();
        $this->postJson(\sprintf('/api/authoring/rules/%s/examples', $ruleId), $payload);
    }
}
