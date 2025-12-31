<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\Authoring\UserInterface\Http\Api\Rule;

use Dairectiv\Authoring\Domain\Object\Directive\Event\DirectiveUpdated;
use Dairectiv\Authoring\Domain\Object\Rule\Example\Example;
use Dairectiv\Authoring\Domain\Object\Rule\Rule;
use Dairectiv\SharedKernel\Domain\Object\Event\DomainEventQueue;
use Dairectiv\Tests\Framework\IntegrationTestCase;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Response;

#[Group('integration')]
#[Group('authoring')]
#[Group('api')]
final class RemoveRuleExampleTest extends IntegrationTestCase
{
    public function testItShouldRemoveExample(): void
    {
        $rule = self::draftRuleEntity();
        $example = Example::create($rule, 'Good', 'Bad', 'Explanation');
        $this->persistEntity($rule);

        self::assertCount(1, $rule->examples);

        $this->removeExample((string) $rule->id, $example->id->toString());

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $persistedRule = $this->findEntity(Rule::class, ['id' => $rule->id], true);

        self::assertCount(0, $persistedRule->examples);
    }

    public function testItShouldRemoveOneExampleFromMultiple(): void
    {
        $rule = self::draftRuleEntity();
        Example::create($rule, 'Good 1', 'Bad 1');
        $example2 = Example::create($rule, 'Good 2', 'Bad 2');
        Example::create($rule, 'Good 3', 'Bad 3');
        $this->persistEntity($rule);

        self::assertCount(3, $rule->examples);

        $this->removeExample((string) $rule->id, $example2->id->toString());

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $persistedRule = $this->findEntity(Rule::class, ['id' => $rule->id], true);

        self::assertCount(2, $persistedRule->examples);

        $remainingGoods = $persistedRule->examples->map(static fn ($e) => $e->good)->toArray();
        self::assertContains('Good 1', $remainingGoods);
        self::assertContains('Good 3', $remainingGoods);
        self::assertNotContains('Good 2', $remainingGoods);
    }

    public function testItShouldReturn404WhenRuleNotFound(): void
    {
        $this->removeExample('non-existent-rule', '00000000-0000-0000-0000-000000000000');

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testItShouldReturn404WhenExampleNotFound(): void
    {
        $rule = self::draftRuleEntity();
        $this->persistEntity($rule);

        $this->removeExample((string) $rule->id, '00000000-0000-0000-0000-000000000000');

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testItShouldReturn404WhenRuleIsArchived(): void
    {
        $rule = self::draftRuleEntity();
        $example = Example::create($rule, 'Good', 'Bad');
        $rule->archive();
        $this->persistEntity($rule);

        $this->removeExample((string) $rule->id, $example->id->toString());

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    private function removeExample(string $ruleId, string $exampleId): void
    {
        DomainEventQueue::reset();
        $this->deleteJson(\sprintf('/api/authoring/rules/%s/examples/%s', $ruleId, $exampleId));
    }
}
