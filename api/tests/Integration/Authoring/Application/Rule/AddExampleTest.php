<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\Authoring\Application\Rule;

use Dairectiv\Authoring\Application\Rule\AddExample\Input;
use Dairectiv\Authoring\Application\Rule\AddExample\Output;
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
final class AddExampleTest extends IntegrationTestCase
{
    public function testItShouldAddExampleToRule(): void
    {
        $rule = self::draftRule();
        $this->persistEntity($rule);

        self::assertTrue($rule->examples->isEmpty());

        $output = $this->execute(new Input(
            (string) $rule->id,
            good: 'Good example',
            bad: 'Bad example',
            explanation: 'Explanation',
        ));

        self::assertInstanceOf(Output::class, $output);
        self::assertInstanceOf(Example::class, $output->example);
        self::assertSame('Good example', $output->example->good);
        self::assertSame('Bad example', $output->example->bad);
        self::assertSame('Explanation', $output->example->explanation);

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $persistedRule = $this->findEntity(Rule::class, ['id' => $rule->id], true);

        self::assertCount(1, $persistedRule->examples);
        $persistedExample = $persistedRule->examples->first();
        self::assertSame('Good example', $persistedExample->good);
        self::assertSame('Bad example', $persistedExample->bad);
        self::assertSame('Explanation', $persistedExample->explanation);
    }

    public function testItShouldAddExampleWithOnlyGood(): void
    {
        $rule = self::draftRule();
        $this->persistEntity($rule);

        $output = $this->execute(new Input(
            (string) $rule->id,
            good: 'Good example only',
        ));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        self::assertSame('Good example only', $output->example->good);
        self::assertNull($output->example->bad);
        self::assertNull($output->example->explanation);
    }

    public function testItShouldAddExampleWithOnlyBad(): void
    {
        $rule = self::draftRule();
        $this->persistEntity($rule);

        $output = $this->execute(new Input(
            (string) $rule->id,
            bad: 'Bad example only',
        ));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        self::assertNull($output->example->good);
        self::assertSame('Bad example only', $output->example->bad);
        self::assertNull($output->example->explanation);
    }

    public function testItShouldAddMultipleExamples(): void
    {
        $rule = self::draftRule();
        $this->persistEntity($rule);

        $output1 = $this->execute(new Input((string) $rule->id, good: 'Good 1'));
        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $output2 = $this->execute(new Input((string) $rule->id, good: 'Good 2'));
        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $output3 = $this->execute(new Input((string) $rule->id, bad: 'Bad 3'));
        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $persistedRule = $this->findEntity(Rule::class, ['id' => $rule->id], true);

        self::assertCount(3, $persistedRule->examples);
    }

    public function testItShouldThrowExceptionWhenRuleNotFound(): void
    {
        $this->expectException(DirectiveNotFoundException::class);

        $this->execute(new Input('non-existent-rule', good: 'Good'));
    }

    public function testItShouldThrowExceptionWhenRuleIsArchived(): void
    {
        $rule = self::draftRule();
        $rule->archive();
        $this->persistEntity($rule);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot perform this action on an archived directive.');

        $this->execute(new Input((string) $rule->id, good: 'Good'));
    }

    public function testItShouldGenerateUniqueExampleId(): void
    {
        $rule = self::draftRule();
        $this->persistEntity($rule);

        $output1 = $this->execute(new Input((string) $rule->id, good: 'Good 1'));
        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $output2 = $this->execute(new Input((string) $rule->id, good: 'Good 2'));
        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        self::assertFalse($output1->example->id->equals($output2->example->id));
    }

    public function testItShouldLinkExampleToCorrectRule(): void
    {
        $rule = self::draftRule();
        $this->persistEntity($rule);

        $output = $this->execute(new Input((string) $rule->id, good: 'Good'));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        self::assertTrue($output->example->rule->id->equals($rule->id));
    }

    public function testItShouldPersistExampleWithCorrectTimestamps(): void
    {
        $rule = self::draftRule();
        $this->persistEntity($rule);

        $output = $this->execute(new Input((string) $rule->id, good: 'Good'));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        self::assertNotNull($output->example->createdAt);
        self::assertNotNull($output->example->updatedAt);

        $persistedRule = $this->findEntity(Rule::class, ['id' => $rule->id], true);
        $persistedExample = $persistedRule->examples->first();

        self::assertNotNull($persistedExample->createdAt);
        self::assertNotNull($persistedExample->updatedAt);
    }
}
