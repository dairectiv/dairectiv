<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\Authoring\Application\Rule;

use Dairectiv\Authoring\Application\Rule\UpdateExample\Input;
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
final class UpdateExampleTest extends IntegrationTestCase
{
    public function testItShouldUpdateAllExampleFields(): void
    {
        $rule = self::draftRule();
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

    public function testItShouldUpdateGoodOnly(): void
    {
        $rule = self::draftRule();
        $example = Example::create($rule, 'Original good', 'Original bad', 'Original explanation');
        $this->persistEntity($rule);

        $this->execute(new Input(
            (string) $rule->id,
            (string) $example->id,
            good: 'Updated good',
        ));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $persistedRule = $this->findEntity(Rule::class, ['id' => $rule->id], true);
        $persistedExample = $persistedRule->examples->first();

        self::assertInstanceOf(Example::class, $persistedExample);
        self::assertSame('Updated good', $persistedExample->good);
        self::assertSame('Original bad', $persistedExample->bad);
        self::assertSame('Original explanation', $persistedExample->explanation);
    }

    public function testItShouldUpdateBadOnly(): void
    {
        $rule = self::draftRule();
        $example = Example::create($rule, 'Original good', 'Original bad');
        $this->persistEntity($rule);

        $this->execute(new Input(
            (string) $rule->id,
            (string) $example->id,
            bad: 'Updated bad',
        ));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $persistedRule = $this->findEntity(Rule::class, ['id' => $rule->id], true);
        $persistedExample = $persistedRule->examples->first();

        self::assertInstanceOf(Example::class, $persistedExample);
        self::assertSame('Original good', $persistedExample->good);
        self::assertSame('Updated bad', $persistedExample->bad);
    }

    public function testItShouldUpdateExplanationOnly(): void
    {
        $rule = self::draftRule();
        $example = Example::create($rule, 'Good', 'Bad');
        $this->persistEntity($rule);

        $this->execute(new Input(
            (string) $rule->id,
            (string) $example->id,
            explanation: 'New explanation',
        ));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $persistedRule = $this->findEntity(Rule::class, ['id' => $rule->id], true);
        $persistedExample = $persistedRule->examples->first();

        self::assertInstanceOf(Example::class, $persistedExample);
        self::assertSame('New explanation', $persistedExample->explanation);
    }

    public function testItShouldThrowExceptionWhenRuleNotFound(): void
    {
        $this->expectException(DirectiveNotFoundException::class);

        $this->execute(new Input(
            'non-existent-rule',
            '00000000-0000-0000-0000-000000000000',
            good: 'Updated',
        ));
    }

    public function testItShouldThrowExceptionWhenExampleNotFound(): void
    {
        $rule = self::draftRule();
        $this->persistEntity($rule);

        $nonExistentId = '00000000-0000-0000-0000-000000000000';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(\sprintf('Example with ID "%s" not found.', $nonExistentId));

        $this->execute(new Input(
            (string) $rule->id,
            $nonExistentId,
            good: 'Updated',
        ));
    }

    public function testItShouldThrowExceptionWhenNoFieldsProvided(): void
    {
        $rule = self::draftRule();
        $example = Example::create($rule, 'Good', 'Bad');
        $this->persistEntity($rule);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('At least one field must be provided.');

        $this->execute(new Input((string) $rule->id, (string) $example->id));
    }

    public function testItShouldThrowExceptionWhenRuleIsArchived(): void
    {
        $rule = self::draftRule();
        $example = Example::create($rule, 'Good', 'Bad');
        $rule->archive();
        $this->persistEntity($rule);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot perform this action on an archived directive.');

        $this->execute(new Input(
            (string) $rule->id,
            (string) $example->id,
            good: 'Updated',
        ));
    }
}
