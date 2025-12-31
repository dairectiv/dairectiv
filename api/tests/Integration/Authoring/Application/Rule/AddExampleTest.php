<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\Authoring\Application\Rule;

use Cake\Chronos\Chronos;
use Dairectiv\Authoring\Application\Rule\AddExample\Input;
use Dairectiv\Authoring\Application\Rule\AddExample\Output;
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
final class AddExampleTest extends IntegrationTestCase
{
    public function testItShouldAddExampleToRule(): void
    {
        $rule = self::draftRuleEntity();
        $this->persistEntity($rule);

        self::assertTrue($rule->examples->isEmpty());

        $output = $this->execute(new Input(
            (string) $rule->id,
            good: 'Good example',
            bad: 'Bad example',
            explanation: 'Explanation',
        ));

        self::assertInstanceOf(Output::class, $output);
        self::assertSame('Good example', $output->example->good);
        self::assertSame('Bad example', $output->example->bad);
        self::assertSame('Explanation', $output->example->explanation);

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $persistedRule = $this->findEntity(Rule::class, ['id' => $rule->id], true);

        self::assertCount(1, $persistedRule->examples);
        $persistedExample = $persistedRule->examples->first();
        self::assertInstanceOf(Example::class, $persistedExample);
        self::assertSame('Good example', $persistedExample->good);
        self::assertSame('Bad example', $persistedExample->bad);
        self::assertSame('Explanation', $persistedExample->explanation);
    }

    public function testItShouldAddExampleWithOnlyGood(): void
    {
        $rule = self::draftRuleEntity();
        $this->persistEntity($rule);

        $output = $this->execute(new Input(
            (string) $rule->id,
            good: 'Good example only',
        ));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        self::assertInstanceOf(Output::class, $output);
        self::assertSame('Good example only', $output->example->good);
        self::assertNull($output->example->bad);
        self::assertNull($output->example->explanation);
    }

    public function testItShouldAddExampleWithOnlyBad(): void
    {
        $rule = self::draftRuleEntity();
        $this->persistEntity($rule);

        $output = $this->execute(new Input(
            (string) $rule->id,
            bad: 'Bad example only',
        ));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        self::assertInstanceOf(Output::class, $output);
        self::assertNull($output->example->good);
        self::assertSame('Bad example only', $output->example->bad);
        self::assertNull($output->example->explanation);
    }

    public function testItShouldAddMultipleExamples(): void
    {
        $rule = self::draftRuleEntity();
        $this->persistEntity($rule);

        $this->execute(new Input((string) $rule->id, good: 'Good 1'));
        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $this->execute(new Input((string) $rule->id, good: 'Good 2'));
        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $this->execute(new Input((string) $rule->id, bad: 'Bad 3'));
        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $persistedRule = $this->findEntity(Rule::class, ['id' => $rule->id], true);

        self::assertCount(3, $persistedRule->examples);
    }

    public function testItShouldThrowExceptionWhenRuleNotFound(): void
    {
        $this->expectException(RuleNotFoundException::class);

        $this->execute(new Input('non-existent-rule', good: 'Good'));
    }

    public function testItShouldThrowExceptionWhenRuleIsArchived(): void
    {
        $rule = self::draftRuleEntity();
        $rule->archive();
        $this->persistEntity($rule);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot perform this action on an archived directive.');

        $this->execute(new Input((string) $rule->id, good: 'Good'));
    }

    public function testItShouldGenerateUniqueExampleId(): void
    {
        $rule = self::draftRuleEntity();
        $this->persistEntity($rule);

        $output1 = $this->execute(new Input((string) $rule->id, good: 'Good 1'));
        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);
        self::assertInstanceOf(Output::class, $output1);

        $output2 = $this->execute(new Input((string) $rule->id, good: 'Good 2'));
        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);
        self::assertInstanceOf(Output::class, $output2);

        self::assertFalse($output1->example->id->equals($output2->example->id));
    }

    public function testItShouldLinkExampleToCorrectRule(): void
    {
        $rule = self::draftRuleEntity();
        $this->persistEntity($rule);

        $output = $this->execute(new Input((string) $rule->id, good: 'Good'));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        self::assertInstanceOf(Output::class, $output);
        self::assertTrue($output->example->rule->id->equals($rule->id));
    }

    public function testItShouldPersistExampleWithCorrectTimestamps(): void
    {
        $rule = self::draftRuleEntity();
        $this->persistEntity($rule);

        Chronos::setTestNow(Chronos::now()->addDays(1));

        $output = $this->execute(new Input((string) $rule->id, good: 'Good'));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        self::assertInstanceOf(Output::class, $output);
        self::assertTrue(Chronos::now()->equals($output->example->createdAt));
        self::assertTrue(Chronos::now()->equals($output->example->updatedAt));

        $persistedRule = $this->findEntity(Rule::class, ['id' => $rule->id], true);
        $persistedExample = $persistedRule->examples->first();

        self::assertInstanceOf(Example::class, $persistedExample);
        self::assertTrue($persistedRule->createdAt->lessThan($persistedExample->createdAt));
        self::assertTrue($persistedRule->createdAt->lessThan($persistedExample->updatedAt));
    }
}
