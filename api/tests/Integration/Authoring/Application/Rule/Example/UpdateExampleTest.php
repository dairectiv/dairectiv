<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\Authoring\Application\Rule\Example;

use Dairectiv\Authoring\Application\Rule\Example\UpdateExample\Input;
use Dairectiv\Authoring\Domain\Object\Directive\Event\DirectiveUpdated;
use Dairectiv\Authoring\Domain\Object\Rule\Example\Example;
use Dairectiv\Authoring\Domain\Object\Rule\Exception\RuleNotFoundException;
use Dairectiv\Authoring\Domain\Object\Rule\Rule;
use Dairectiv\SharedKernel\Domain\Object\Exception\InvalidArgumentException;
use Dairectiv\Tests\Framework\IntegrationTestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('integration')]
#[Group('authoring')]
#[Group('use-case')]
final class UpdateExampleTest extends IntegrationTestCase
{
    public function testItShouldUpdateAllExampleFields(): void
    {
        $rule = self::draftRuleEntity();
        $example = Example::create($rule, 'Original good', 'Original bad', 'Original explanation');
        $this->persistEntity($rule);

        $this->execute(new Input(
            (string) $rule->id,
            (string) $example->id,
            good: 'Updated good',
            bad: 'Updated bad',
            explanation: 'Updated explanation',
        ));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $persistedRule = $this->findEntity(Rule::class, ['id' => $rule->id], true);
        $persistedExample = $persistedRule->examples->first();

        self::assertInstanceOf(Example::class, $persistedExample);
        self::assertSame('Updated good', $persistedExample->good);
        self::assertSame('Updated bad', $persistedExample->bad);
        self::assertSame('Updated explanation', $persistedExample->explanation);
    }

    public function testItShouldClearGoodField(): void
    {
        $rule = self::draftRuleEntity();
        $example = Example::create($rule, 'Original good', 'Original bad', 'Original explanation');
        $this->persistEntity($rule);

        $this->execute(new Input(
            (string) $rule->id,
            (string) $example->id,
            good: null,
            bad: 'Updated bad',
            explanation: 'Updated explanation',
        ));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $persistedRule = $this->findEntity(Rule::class, ['id' => $rule->id], true);
        $persistedExample = $persistedRule->examples->first();

        self::assertInstanceOf(Example::class, $persistedExample);
        self::assertNull($persistedExample->good);
        self::assertSame('Updated bad', $persistedExample->bad);
        self::assertSame('Updated explanation', $persistedExample->explanation);
    }

    public function testItShouldClearBadField(): void
    {
        $rule = self::draftRuleEntity();
        $example = Example::create($rule, 'Original good', 'Original bad', 'Original explanation');
        $this->persistEntity($rule);

        $this->execute(new Input(
            (string) $rule->id,
            (string) $example->id,
            good: 'Updated good',
            bad: null,
            explanation: 'Updated explanation',
        ));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $persistedRule = $this->findEntity(Rule::class, ['id' => $rule->id], true);
        $persistedExample = $persistedRule->examples->first();

        self::assertInstanceOf(Example::class, $persistedExample);
        self::assertSame('Updated good', $persistedExample->good);
        self::assertNull($persistedExample->bad);
        self::assertSame('Updated explanation', $persistedExample->explanation);
    }

    public function testItShouldClearAllOptionalFields(): void
    {
        $rule = self::draftRuleEntity();
        $example = Example::create($rule, 'Original good', 'Original bad', 'Original explanation');
        $this->persistEntity($rule);

        $this->execute(new Input(
            (string) $rule->id,
            (string) $example->id,
            good: null,
            bad: null,
            explanation: null,
        ));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $persistedRule = $this->findEntity(Rule::class, ['id' => $rule->id], true);
        $persistedExample = $persistedRule->examples->first();

        self::assertInstanceOf(Example::class, $persistedExample);
        self::assertNull($persistedExample->good);
        self::assertNull($persistedExample->bad);
        self::assertNull($persistedExample->explanation);
    }

    public function testItShouldThrowExceptionWhenRuleNotFound(): void
    {
        $this->expectException(RuleNotFoundException::class);

        $this->execute(new Input(
            'non-existent-rule',
            '00000000-0000-0000-0000-000000000000',
            good: 'Updated',
            bad: null,
            explanation: null,
        ));
    }

    public function testItShouldThrowExceptionWhenExampleNotFound(): void
    {
        $rule = self::draftRuleEntity();
        $this->persistEntity($rule);

        $nonExistentId = '00000000-0000-0000-0000-000000000000';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(\sprintf('Example with ID "%s" not found.', $nonExistentId));

        $this->execute(new Input(
            (string) $rule->id,
            $nonExistentId,
            good: 'Updated',
            bad: null,
            explanation: null,
        ));
    }

    public function testItShouldThrowExceptionWhenRuleIsArchived(): void
    {
        $rule = self::draftRuleEntity();
        $example = Example::create($rule, 'Good', 'Bad');
        $rule->archive();
        $this->persistEntity($rule);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot perform this action on an archived directive.');

        $this->execute(new Input(
            (string) $rule->id,
            (string) $example->id,
            good: 'Updated',
            bad: null,
            explanation: null,
        ));
    }
}
