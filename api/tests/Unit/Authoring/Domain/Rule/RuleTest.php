<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Unit\Authoring\Domain\Rule;

use Cake\Chronos\Chronos;
use Dairectiv\Authoring\Domain\Directive\DirectiveId;
use Dairectiv\Authoring\Domain\Directive\DirectiveName;
use Dairectiv\Authoring\Domain\Directive\DirectiveState;
use Dairectiv\Authoring\Domain\Directive\DirectiveVersion;
use Dairectiv\Authoring\Domain\Directive\Event\DirectiveArchived;
use Dairectiv\Authoring\Domain\Directive\Event\DirectiveDrafted;
use Dairectiv\Authoring\Domain\Directive\Event\DirectivePublished;
use Dairectiv\Authoring\Domain\Directive\Event\DirectiveUpdated;
use Dairectiv\Authoring\Domain\Directive\Exception\DirectiveConflictException;
use Dairectiv\Authoring\Domain\Rule\Rule;
use Dairectiv\Authoring\Domain\Rule\RuleChange;
use Dairectiv\Tests\Framework\AggregateRootAssertions;
use PHPUnit\Framework\TestCase;

final class RuleTest extends TestCase
{
    use AggregateRootAssertions;

    public function testItShouldCreateDraftRuleWithInitialState(): void
    {
        $id = DirectiveId::fromString('my-rule');
        $name = DirectiveName::fromString('my-rule-name');

        $rule = Rule::draft($id, $name);

        self::assertSame($id, $rule->id);
        self::assertSame($name, $rule->name);
        self::assertSame('my-rule-name', (string) $rule->name);
        self::assertSame(DirectiveState::Draft, $rule->state);
        self::assertSame(1, $rule->version->version);

        $this->assertDomainEventRecorded(DirectiveDrafted::class);
    }

    public function testItShouldRecordDirectiveDraftedEventWhenCreating(): void
    {
        $id = DirectiveId::fromString('my-rule');
        $name = DirectiveName::fromString('my-rule-name');

        Rule::draft($id, $name);

        $event = $this->assertDomainEventRecorded(DirectiveDrafted::class);

        self::assertSame($id, $event->directiveId);
    }

    public function testItShouldHaveSameTimestampForCreatedAtAndUpdatedAtWhenDrafting(): void
    {
        $id = DirectiveId::fromString('my-rule');
        $name = DirectiveName::fromString('my-rule-name');

        $rule = Rule::draft($id, $name);

        self::assertTrue($rule->createdAt->equals($rule->updatedAt));

        $this->assertDomainEventRecorded(DirectiveDrafted::class);
    }

    public function testItShouldIncrementVersionWhenApplyingChanges(): void
    {
        $id = DirectiveId::fromString('my-rule');
        $name = DirectiveName::fromString('my-rule-name');
        $rule = Rule::draft($id, $name);

        $this->resetDomainEvents();

        $change = new RuleChange();
        $rule->applyChanges($change, DirectiveVersion::initial());

        self::assertSame(2, $rule->version->version);

        $this->assertDomainEventRecorded(DirectiveUpdated::class);
    }

    public function testItShouldUpdateUpdatedAtWhenApplyingChanges(): void
    {
        $id = DirectiveId::fromString('my-rule');
        $name = DirectiveName::fromString('my-rule-name');
        $rule = Rule::draft($id, $name);

        $initialUpdatedAt = $rule->updatedAt;

        Chronos::setTestNow(Chronos::now()->addMinutes(5));
        $this->resetDomainEvents();

        $change = new RuleChange();
        $rule->applyChanges($change, DirectiveVersion::initial());

        self::assertFalse($rule->updatedAt->equals($initialUpdatedAt));
        self::assertTrue($rule->updatedAt->greaterThan($initialUpdatedAt));

        $this->assertDomainEventRecorded(DirectiveUpdated::class);
    }

    public function testItShouldRecordDirectiveUpdatedEventWhenApplyingChanges(): void
    {
        $id = DirectiveId::fromString('my-rule');
        $name = DirectiveName::fromString('my-rule-name');
        $rule = Rule::draft($id, $name);

        $this->resetDomainEvents();

        $change = new RuleChange();
        $rule->applyChanges($change, DirectiveVersion::initial());

        $event = $this->assertDomainEventRecorded(DirectiveUpdated::class);

        self::assertSame($id, $event->directiveId);
        self::assertSame(2, $event->directiveVersion->version);
    }

    public function testItShouldThrowExceptionWhenApplyingChangesWithIncorrectVersion(): void
    {
        $id = DirectiveId::fromString('my-rule');
        $name = DirectiveName::fromString('my-rule-name');
        $rule = Rule::draft($id, $name);

        $this->assertDomainEventRecorded(DirectiveDrafted::class);

        $incorrectVersion = DirectiveVersion::initial()->increment();

        $this->expectException(DirectiveConflictException::class);

        $change = new RuleChange();
        $rule->applyChanges($change, $incorrectVersion);
    }

    public function testItShouldAllowMultipleChangesWithCorrectVersions(): void
    {
        $id = DirectiveId::fromString('my-rule');
        $name = DirectiveName::fromString('my-rule-name');
        $rule = Rule::draft($id, $name);

        $this->resetDomainEvents();

        $rule->applyChanges(new RuleChange(), DirectiveVersion::initial());
        $rule->applyChanges(new RuleChange(), DirectiveVersion::initial()->increment());
        $rule->applyChanges(new RuleChange(), DirectiveVersion::initial()->increment()->increment());

        self::assertSame(4, $rule->version->version);

        $this->assertDomainEventRecorded(DirectiveUpdated::class);
        $this->assertDomainEventRecorded(DirectiveUpdated::class);
        $this->assertDomainEventRecorded(DirectiveUpdated::class);
    }

    public function testItShouldChangeStateToPublishedWhenPublishing(): void
    {
        $id = DirectiveId::fromString('my-rule');
        $name = DirectiveName::fromString('my-rule-name');
        $rule = Rule::draft($id, $name);

        $this->resetDomainEvents();

        $rule->publish();

        self::assertSame(DirectiveState::Published, $rule->state);

        $this->assertDomainEventRecorded(DirectivePublished::class);
    }

    public function testItShouldUpdateUpdatedAtWhenPublishing(): void
    {
        $id = DirectiveId::fromString('my-rule');
        $name = DirectiveName::fromString('my-rule-name');
        $rule = Rule::draft($id, $name);

        $initialUpdatedAt = $rule->updatedAt;

        Chronos::setTestNow(Chronos::now()->addMinutes(5));
        $this->resetDomainEvents();

        $rule->publish();

        self::assertFalse($rule->updatedAt->equals($initialUpdatedAt));
        self::assertTrue($rule->updatedAt->greaterThan($initialUpdatedAt));

        $this->assertDomainEventRecorded(DirectivePublished::class);
    }

    public function testItShouldRecordDirectivePublishedEventWhenPublishing(): void
    {
        $id = DirectiveId::fromString('my-rule');
        $name = DirectiveName::fromString('my-rule-name');
        $rule = Rule::draft($id, $name);

        $this->resetDomainEvents();

        $rule->publish();

        $event = $this->assertDomainEventRecorded(DirectivePublished::class);

        self::assertSame($id, $event->directiveId);
    }

    public function testItShouldNotIncrementVersionWhenPublishing(): void
    {
        $id = DirectiveId::fromString('my-rule');
        $name = DirectiveName::fromString('my-rule-name');
        $rule = Rule::draft($id, $name);

        $initialVersion = $rule->version;

        $this->resetDomainEvents();

        $rule->publish();

        self::assertSame($initialVersion, $rule->version);

        $this->assertDomainEventRecorded(DirectivePublished::class);
    }

    public function testItShouldChangeStateToArchivedWhenArchiving(): void
    {
        $id = DirectiveId::fromString('my-rule');
        $name = DirectiveName::fromString('my-rule-name');
        $rule = Rule::draft($id, $name);

        $this->resetDomainEvents();

        $rule->archive();

        self::assertSame(DirectiveState::Archived, $rule->state);

        $this->assertDomainEventRecorded(DirectiveArchived::class);
    }

    public function testItShouldUpdateUpdatedAtWhenArchiving(): void
    {
        $id = DirectiveId::fromString('my-rule');
        $name = DirectiveName::fromString('my-rule-name');
        $rule = Rule::draft($id, $name);

        $initialUpdatedAt = $rule->updatedAt;

        Chronos::setTestNow(Chronos::now()->addMinutes(5));
        $this->resetDomainEvents();

        $rule->archive();

        self::assertFalse($rule->updatedAt->equals($initialUpdatedAt));
        self::assertTrue($rule->updatedAt->greaterThan($initialUpdatedAt));

        $this->assertDomainEventRecorded(DirectiveArchived::class);
    }

    public function testItShouldRecordDirectiveArchivedEventWhenArchiving(): void
    {
        $id = DirectiveId::fromString('my-rule');
        $name = DirectiveName::fromString('my-rule-name');
        $rule = Rule::draft($id, $name);

        $this->resetDomainEvents();

        $rule->archive();

        $event = $this->assertDomainEventRecorded(DirectiveArchived::class);

        self::assertSame($id, $event->directiveId);
    }

    public function testItShouldNotIncrementVersionWhenArchiving(): void
    {
        $id = DirectiveId::fromString('my-rule');
        $name = DirectiveName::fromString('my-rule-name');
        $rule = Rule::draft($id, $name);

        $initialVersion = $rule->version;

        $this->resetDomainEvents();

        $rule->archive();

        self::assertSame($initialVersion, $rule->version);

        $this->assertDomainEventRecorded(DirectiveArchived::class);
    }

    public function testItShouldAllowPublishingDraftRule(): void
    {
        $id = DirectiveId::fromString('my-rule');
        $name = DirectiveName::fromString('my-rule-name');
        $rule = Rule::draft($id, $name);

        $this->resetDomainEvents();

        $rule->publish();

        self::assertSame(DirectiveState::Published, $rule->state);

        $this->assertDomainEventRecorded(DirectivePublished::class);
    }

    public function testItShouldAllowArchivingPublishedRule(): void
    {
        $id = DirectiveId::fromString('my-rule');
        $name = DirectiveName::fromString('my-rule-name');
        $rule = Rule::draft($id, $name);

        $rule->publish();

        $this->resetDomainEvents();

        $rule->archive();

        self::assertSame(DirectiveState::Archived, $rule->state);

        $this->assertDomainEventRecorded(DirectiveArchived::class);
    }

    public function testItShouldAllowArchivingDraftRule(): void
    {
        $id = DirectiveId::fromString('my-rule');
        $name = DirectiveName::fromString('my-rule-name');
        $rule = Rule::draft($id, $name);

        $this->resetDomainEvents();

        $rule->archive();

        self::assertSame(DirectiveState::Archived, $rule->state);

        $this->assertDomainEventRecorded(DirectiveArchived::class);
    }

    public function testItShouldMaintainCreatedAtThroughLifecycle(): void
    {
        $id = DirectiveId::fromString('my-rule');
        $name = DirectiveName::fromString('my-rule-name');
        $rule = Rule::draft($id, $name);

        $createdAt = $rule->createdAt;

        Chronos::setTestNow(Chronos::now()->addDays(1));

        $this->resetDomainEvents();

        $rule->applyChanges(new RuleChange(), DirectiveVersion::initial());
        $rule->publish();
        $rule->archive();

        self::assertTrue($rule->createdAt->equals($createdAt));

        $this->assertDomainEventRecorded(DirectiveUpdated::class);
        $this->assertDomainEventRecorded(DirectivePublished::class);
        $this->assertDomainEventRecorded(DirectiveArchived::class);
    }
}
