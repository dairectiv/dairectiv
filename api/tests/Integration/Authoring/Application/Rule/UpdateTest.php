<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\Authoring\Application\Rule;

use Cake\Chronos\Chronos;
use Dairectiv\Authoring\Application\Rule\Update\Input;
use Dairectiv\Authoring\Domain\Object\Directive\Event\DirectiveUpdated;
use Dairectiv\Authoring\Domain\Object\Rule\Exception\RuleNotFoundException;
use Dairectiv\Authoring\Domain\Object\Rule\Rule;
use Dairectiv\SharedKernel\Domain\Object\Exception\InvalidArgumentException;
use Dairectiv\Tests\Framework\IntegrationTestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('integration')]
#[Group('authoring')]
#[Group('use-case')]
final class UpdateTest extends IntegrationTestCase
{
    public function testItShouldUpdateRuleName(): void
    {
        $rule = self::draftRuleEntity(name: 'Original Name', description: 'Original description');
        $this->persistEntity($rule);

        $this->execute(new Input((string) $rule->id, name: 'Updated Name'));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $persistedRule = $this->findEntity(Rule::class, ['id' => $rule->id], true);

        self::assertSame('Updated Name', $persistedRule->name);
        self::assertSame('Original description', $persistedRule->description);
    }

    public function testItShouldUpdateRuleDescription(): void
    {
        $rule = self::draftRuleEntity(description: 'Original description');
        $this->persistEntity($rule);

        $this->execute(new Input((string) $rule->id, description: 'Updated description'));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $persistedRule = $this->findEntity(Rule::class, ['id' => $rule->id], true);

        self::assertSame('Updated description', $persistedRule->description);
    }

    public function testItShouldUpdateRuleContent(): void
    {
        $rule = self::draftRuleEntity();
        $this->persistEntity($rule);

        self::assertNull($rule->content);

        $this->execute(new Input((string) $rule->id, content: 'New content'));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $persistedRule = $this->findEntity(Rule::class, ['id' => $rule->id], true);

        self::assertSame('New content', $persistedRule->content);
    }

    public function testItShouldUpdateAllFields(): void
    {
        $rule = self::draftRuleEntity();
        $this->persistEntity($rule);

        $this->execute(new Input(
            (string) $rule->id,
            name: 'New Name',
            description: 'New description',
            content: 'New content',
        ));

        // Two events: one from metadata update, one from content update
        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class, 2);

        $persistedRule = $this->findEntity(Rule::class, ['id' => $rule->id], true);

        self::assertSame('New Name', $persistedRule->name);
        self::assertSame('New description', $persistedRule->description);
        self::assertSame('New content', $persistedRule->content);
    }

    public function testItShouldThrowExceptionWhenNoFieldsProvided(): void
    {
        $rule = self::draftRuleEntity();
        $this->persistEntity($rule);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('At least one field must be provided.');

        $this->execute(new Input((string) $rule->id));
    }

    public function testItShouldThrowExceptionWhenRuleNotFound(): void
    {
        $this->expectException(RuleNotFoundException::class);

        $this->execute(new Input('non-existent-rule', name: 'Name'));
    }

    public function testItShouldThrowExceptionWhenRuleIsArchived(): void
    {
        $rule = self::draftRuleEntity();
        $rule->archive();
        $this->persistEntity($rule);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot perform this action on an archived directive.');

        $this->execute(new Input((string) $rule->id, name: 'New Name'));
    }

    public function testItShouldUpdateRuleTimestamp(): void
    {
        $rule = self::draftRuleEntity();
        $this->persistEntity($rule);

        Chronos::setTestNow(Chronos::now()->addDays(1));

        $this->execute(new Input((string) $rule->id, content: 'New content'));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $persistedRule = $this->findEntity(Rule::class, ['id' => $rule->id], true);

        self::assertTrue($persistedRule->updatedAt->greaterThan($persistedRule->createdAt));
    }
}
