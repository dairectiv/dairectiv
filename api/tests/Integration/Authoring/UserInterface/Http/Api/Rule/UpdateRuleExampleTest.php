<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\Authoring\UserInterface\Http\Api\Rule;

use Cake\Chronos\Chronos;
use Dairectiv\Authoring\Domain\Object\Directive\Event\DirectiveUpdated;
use Dairectiv\Authoring\Domain\Object\Rule\Example\Example;
use Dairectiv\SharedKernel\Domain\Object\Event\DomainEventQueue;
use Dairectiv\Tests\Framework\IntegrationTestCase;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Response;

#[Group('integration')]
#[Group('authoring')]
#[Group('api')]
final class UpdateRuleExampleTest extends IntegrationTestCase
{
    public function testItShouldUpdateAllExampleFields(): void
    {
        $rule = self::draftRuleEntity();
        $example = Example::create($rule, 'Original good', 'Original bad', 'Original explanation');
        $this->persistEntity($rule);

        $this->updateExample((string) $rule->id, $example->id->toString(), [
            'good'        => 'Updated good',
            'bad'         => 'Updated bad',
            'explanation' => 'Updated explanation',
        ]);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        self::assertResponseReturnsJson([
            'id'          => $example->id->toString(),
            'createdAt'   => Chronos::now()->toIso8601String(),
            'updatedAt'   => Chronos::now()->toIso8601String(),
            'good'        => 'Updated good',
            'bad'         => 'Updated bad',
            'explanation' => 'Updated explanation',
        ]);

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);
    }

    public function testItShouldUpdateGoodOnly(): void
    {
        $rule = self::draftRuleEntity();
        $example = Example::create($rule, 'Original good', 'Original bad', 'Original explanation');
        $this->persistEntity($rule);

        $this->updateExample((string) $rule->id, $example->id->toString(), [
            'good' => 'Updated good',
        ]);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        self::assertResponseReturnsJson([
            'id'          => $example->id->toString(),
            'createdAt'   => Chronos::now()->toIso8601String(),
            'updatedAt'   => Chronos::now()->toIso8601String(),
            'good'        => 'Updated good',
            'bad'         => 'Original bad',
            'explanation' => 'Original explanation',
        ]);

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);
    }

    public function testItShouldUpdateBadOnly(): void
    {
        $rule = self::draftRuleEntity();
        $example = Example::create($rule, 'Original good', 'Original bad', 'Original explanation');
        $this->persistEntity($rule);

        $this->updateExample((string) $rule->id, $example->id->toString(), [
            'bad' => 'Updated bad',
        ]);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        self::assertResponseReturnsJson([
            'id'          => $example->id->toString(),
            'createdAt'   => Chronos::now()->toIso8601String(),
            'updatedAt'   => Chronos::now()->toIso8601String(),
            'good'        => 'Original good',
            'bad'         => 'Updated bad',
            'explanation' => 'Original explanation',
        ]);

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);
    }

    public function testItShouldUpdateExplanationOnly(): void
    {
        $rule = self::draftRuleEntity();
        $example = Example::create($rule, 'Good', 'Bad');
        $this->persistEntity($rule);

        $this->updateExample((string) $rule->id, $example->id->toString(), [
            'explanation' => 'New explanation',
        ]);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        self::assertResponseReturnsJson([
            'id'          => $example->id->toString(),
            'createdAt'   => Chronos::now()->toIso8601String(),
            'updatedAt'   => Chronos::now()->toIso8601String(),
            'good'        => 'Good',
            'bad'         => 'Bad',
            'explanation' => 'New explanation',
        ]);

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);
    }

    public function testItShouldReturn404WhenRuleNotFound(): void
    {
        $this->updateExample('non-existent-rule', '00000000-0000-0000-0000-000000000000', [
            'good' => 'Updated',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testItShouldReturn400WhenExampleNotFound(): void
    {
        $rule = self::draftRuleEntity();
        $this->persistEntity($rule);

        $this->updateExample((string) $rule->id, '00000000-0000-0000-0000-000000000000', [
            'good' => 'Updated',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testItShouldReturn400WhenNoFieldsProvided(): void
    {
        $rule = self::draftRuleEntity();
        $example = Example::create($rule, 'Good', 'Bad');
        $this->persistEntity($rule);

        $this->updateExample((string) $rule->id, $example->id->toString(), []);

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testItShouldReturn400WhenRuleIsArchived(): void
    {
        $rule = self::draftRuleEntity();
        $example = Example::create($rule, 'Good', 'Bad');
        $rule->archive();
        $this->persistEntity($rule);

        $this->updateExample((string) $rule->id, $example->id->toString(), [
            'good' => 'Updated',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function updateExample(string $ruleId, string $exampleId, array $payload): void
    {
        DomainEventQueue::reset();
        $this->patchJson(\sprintf('/api/authoring/rules/%s/examples/%s', $ruleId, $exampleId), $payload);
    }
}
