<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\Authoring\Application\Rule;

use Dairectiv\Authoring\Application\Rule\RemoveExample\Input;
use Dairectiv\Authoring\Domain\Object\Directive\Event\DirectiveUpdated;
use Dairectiv\Authoring\Domain\Object\Directive\Exception\DirectiveNotFoundException;
use Dairectiv\Authoring\Domain\Object\Rule\Example\Example;
use Dairectiv\Authoring\Domain\Object\Rule\Rule;
use Dairectiv\SharedKernel\Domain\Object\Exception\InvalidArgumentException;
use Dairectiv\Tests\Framework\IntegrationTestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('integration')]
#[Group('authoring')]
#[Group('use-case')]
final class RemoveExampleTest extends IntegrationTestCase
{
    public function testItShouldRemoveExampleFromRule(): void
    {
        $rule = self::draftRule();
        $example = Example::create($rule, 'Good', 'Bad', 'Explanation');
        $this->persistEntity($rule);

        self::assertCount(1, $rule->examples);

        $this->execute(new Input((string) $rule->id, (string) $example->id));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $persistedRule = $this->findEntity(Rule::class, ['id' => $rule->id], true);

        self::assertCount(0, $persistedRule->examples);
    }

    public function testItShouldRemoveOneExampleFromMultiple(): void
    {
        $rule = self::draftRule();
        $example1 = Example::create($rule, 'Good 1', 'Bad 1');
        $example2 = Example::create($rule, 'Good 2', 'Bad 2');
        $example3 = Example::create($rule, 'Good 3', 'Bad 3');
        $this->persistEntity($rule);

        self::assertCount(3, $rule->examples);

        $this->execute(new Input((string) $rule->id, (string) $example2->id));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $persistedRule = $this->findEntity(Rule::class, ['id' => $rule->id], true);

        self::assertCount(2, $persistedRule->examples);

        $remainingGoods = $persistedRule->examples->map(fn ($e) => $e->good)->toArray();
        self::assertContains('Good 1', $remainingGoods);
        self::assertContains('Good 3', $remainingGoods);
        self::assertNotContains('Good 2', $remainingGoods);
    }

    public function testItShouldThrowExceptionWhenRuleNotFound(): void
    {
        $this->expectException(DirectiveNotFoundException::class);

        $this->execute(new Input('non-existent-rule', '00000000-0000-0000-0000-000000000000'));
    }

    public function testItShouldThrowExceptionWhenExampleNotFound(): void
    {
        $rule = self::draftRule();
        $this->persistEntity($rule);

        $nonExistentId = '00000000-0000-0000-0000-000000000000';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(\sprintf('Example with ID "%s" not found.', $nonExistentId));

        $this->execute(new Input((string) $rule->id, $nonExistentId));
    }

    public function testItShouldThrowExceptionWhenRuleIsArchived(): void
    {
        $rule = self::draftRule();
        $example = Example::create($rule, 'Good', 'Bad');
        $rule->archive();
        $this->persistEntity($rule);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot perform this action on an archived directive.');

        $this->execute(new Input((string) $rule->id, (string) $example->id));
    }
}
