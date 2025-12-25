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
use Dairectiv\Authoring\Domain\Rule\RuleContent;
use Dairectiv\Authoring\Domain\Rule\RuleDescription;
use Dairectiv\Authoring\Domain\Rule\RuleExample;
use Dairectiv\Authoring\Domain\Rule\RuleExamples;
use Dairectiv\Tests\Framework\AggregateRootAssertions;
use PHPUnit\Framework\TestCase;

final class RuleTest extends TestCase
{
    use AggregateRootAssertions;

    public function testItShouldCreateDraftRuleWithInitialState(): void
    {
        $id = DirectiveId::fromString('my-rule');
        $name = DirectiveName::fromString('my-rule-name');

        $rule = $this->createRule($id, $name);

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

        $this->createRule($id);

        $event = $this->assertDomainEventRecorded(DirectiveDrafted::class);

        self::assertSame($id, $event->directiveId);
    }

    public function testItShouldHaveSameTimestampForCreatedAtAndUpdatedAtWhenDrafting(): void
    {
        $id = DirectiveId::fromString('my-rule');
        $name = DirectiveName::fromString('my-rule-name');

        $rule = $this->createRule($id, $name);

        self::assertTrue($rule->createdAt->equals($rule->updatedAt));

        $this->assertDomainEventRecorded(DirectiveDrafted::class);
    }

    public function testItShouldIncrementVersionWhenUpdating(): void
    {
        $id = DirectiveId::fromString('my-rule');
        $name = DirectiveName::fromString('my-rule-name');
        $rule = $this->createRule($id, $name);

        $this->resetDomainEvents();

        $rule->update(DirectiveVersion::initial());

        self::assertSame(2, $rule->version->version);

        $this->assertDomainEventRecorded(DirectiveUpdated::class);
    }

    public function testItShouldUpdateUpdatedAtWhenUpdating(): void
    {
        $id = DirectiveId::fromString('my-rule');
        $name = DirectiveName::fromString('my-rule-name');
        $rule = $this->createRule($id, $name);

        $initialUpdatedAt = $rule->updatedAt;

        Chronos::setTestNow(Chronos::now()->addMinutes(5));
        $this->resetDomainEvents();

        $rule->update(DirectiveVersion::initial());

        self::assertFalse($rule->updatedAt->equals($initialUpdatedAt));
        self::assertTrue($rule->updatedAt->greaterThan($initialUpdatedAt));

        $this->assertDomainEventRecorded(DirectiveUpdated::class);
    }

    public function testItShouldRecordDirectiveUpdatedEventWhenUpdating(): void
    {
        $id = DirectiveId::fromString('my-rule');
        $name = DirectiveName::fromString('my-rule-name');
        $rule = $this->createRule($id, $name);

        $this->resetDomainEvents();

        $rule->update(DirectiveVersion::initial());

        $event = $this->assertDomainEventRecorded(DirectiveUpdated::class);

        self::assertSame($id, $event->directiveId);
        self::assertSame(2, $event->directiveVersion->version);
    }

    public function testItShouldThrowExceptionWhenUpdatingWithIncorrectVersion(): void
    {
        $id = DirectiveId::fromString('my-rule');
        $name = DirectiveName::fromString('my-rule-name');
        $rule = $this->createRule($id, $name);

        $this->assertDomainEventRecorded(DirectiveDrafted::class);

        $incorrectVersion = DirectiveVersion::initial()->increment();

        $this->expectException(DirectiveConflictException::class);

        $rule->update($incorrectVersion);
    }

    public function testItShouldAllowMultipleUpdatesWithCorrectVersions(): void
    {
        $id = DirectiveId::fromString('my-rule');
        $name = DirectiveName::fromString('my-rule-name');
        $rule = $this->createRule($id, $name);

        $this->resetDomainEvents();

        $rule->update(DirectiveVersion::initial());
        $rule->update(DirectiveVersion::initial()->increment());
        $rule->update(DirectiveVersion::initial()->increment()->increment());

        self::assertSame(4, $rule->version->version);

        $this->assertDomainEventRecorded(DirectiveUpdated::class);
        $this->assertDomainEventRecorded(DirectiveUpdated::class);
        $this->assertDomainEventRecorded(DirectiveUpdated::class);
    }

    public function testItShouldChangeStateToPublishedWhenPublishing(): void
    {
        $id = DirectiveId::fromString('my-rule');
        $name = DirectiveName::fromString('my-rule-name');
        $rule = $this->createRule($id, $name);

        $this->resetDomainEvents();

        $rule->publish();

        self::assertSame(DirectiveState::Published, $rule->state);

        $this->assertDomainEventRecorded(DirectivePublished::class);
    }

    public function testItShouldUpdateUpdatedAtWhenPublishing(): void
    {
        $id = DirectiveId::fromString('my-rule');
        $name = DirectiveName::fromString('my-rule-name');
        $rule = $this->createRule($id, $name);

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
        $rule = $this->createRule($id, $name);

        $this->resetDomainEvents();

        $rule->publish();

        $event = $this->assertDomainEventRecorded(DirectivePublished::class);

        self::assertSame($id, $event->directiveId);
    }

    public function testItShouldNotIncrementVersionWhenPublishing(): void
    {
        $id = DirectiveId::fromString('my-rule');
        $name = DirectiveName::fromString('my-rule-name');
        $rule = $this->createRule($id, $name);

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
        $rule = $this->createRule($id, $name);

        $this->resetDomainEvents();

        $rule->archive();

        self::assertSame(DirectiveState::Archived, $rule->state);

        $this->assertDomainEventRecorded(DirectiveArchived::class);
    }

    public function testItShouldUpdateUpdatedAtWhenArchiving(): void
    {
        $id = DirectiveId::fromString('my-rule');
        $name = DirectiveName::fromString('my-rule-name');
        $rule = $this->createRule($id, $name);

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
        $rule = $this->createRule($id, $name);

        $this->resetDomainEvents();

        $rule->archive();

        $event = $this->assertDomainEventRecorded(DirectiveArchived::class);

        self::assertSame($id, $event->directiveId);
    }

    public function testItShouldNotIncrementVersionWhenArchiving(): void
    {
        $id = DirectiveId::fromString('my-rule');
        $name = DirectiveName::fromString('my-rule-name');
        $rule = $this->createRule($id, $name);

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
        $rule = $this->createRule($id, $name);

        $this->resetDomainEvents();

        $rule->publish();

        self::assertSame(DirectiveState::Published, $rule->state);

        $this->assertDomainEventRecorded(DirectivePublished::class);
    }

    public function testItShouldAllowArchivingPublishedRule(): void
    {
        $id = DirectiveId::fromString('my-rule');
        $name = DirectiveName::fromString('my-rule-name');
        $rule = $this->createRule($id, $name);

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
        $rule = $this->createRule($id, $name);

        $this->resetDomainEvents();

        $rule->archive();

        self::assertSame(DirectiveState::Archived, $rule->state);

        $this->assertDomainEventRecorded(DirectiveArchived::class);
    }

    public function testItShouldMaintainCreatedAtThroughLifecycle(): void
    {
        $id = DirectiveId::fromString('my-rule');
        $name = DirectiveName::fromString('my-rule-name');
        $rule = $this->createRule($id, $name);

        $createdAt = $rule->createdAt;

        Chronos::setTestNow(Chronos::now()->addDays(1));

        $this->resetDomainEvents();

        $rule->update(DirectiveVersion::initial());
        $rule->publish();
        $rule->archive();

        self::assertTrue($rule->createdAt->equals($createdAt));

        $this->assertDomainEventRecorded(DirectiveUpdated::class);
        $this->assertDomainEventRecorded(DirectivePublished::class);
        $this->assertDomainEventRecorded(DirectiveArchived::class);
    }

    public function testItShouldCreateRuleWithDescriptionAndContent(): void
    {
        $id = DirectiveId::fromString('my-rule');
        $name = DirectiveName::fromString('my-rule-name');
        $description = RuleDescription::fromString('A rule description');
        $content = RuleContent::fromString('## MUST\n- Use sprintf');

        $rule = Rule::draft($id, $name, $description, $content);

        self::assertSame($description, $rule->description);
        self::assertSame($content, $rule->content);
        self::assertTrue($rule->examples->isEmpty());

        $this->assertDomainEventRecorded(DirectiveDrafted::class);
    }

    public function testItShouldCreateRuleWithExamples(): void
    {
        $id = DirectiveId::fromString('my-rule');
        $name = DirectiveName::fromString('my-rule-name');
        $description = RuleDescription::fromString('A rule description');
        $content = RuleContent::fromString('## MUST\n- Use sprintf');
        $examples = RuleExamples::fromArray([
            RuleExample::good('sprintf code'),
            RuleExample::bad('interpolation code'),
        ]);

        $rule = Rule::draft($id, $name, $description, $content, $examples);

        self::assertCount(2, $rule->examples);

        $this->assertDomainEventRecorded(DirectiveDrafted::class);
    }

    public function testItShouldUpdateDescription(): void
    {
        $rule = $this->createRule();

        $this->resetDomainEvents();

        $newDescription = RuleDescription::fromString('Updated description');
        $rule->update(DirectiveVersion::initial(), description: $newDescription);

        self::assertSame($newDescription, $rule->description);

        $this->assertDomainEventRecorded(DirectiveUpdated::class);
    }

    public function testItShouldUpdateContent(): void
    {
        $rule = $this->createRule();

        $this->resetDomainEvents();

        $newContent = RuleContent::fromString('Updated content');
        $rule->update(DirectiveVersion::initial(), content: $newContent);

        self::assertSame($newContent, $rule->content);

        $this->assertDomainEventRecorded(DirectiveUpdated::class);
    }

    public function testItShouldUpdateExamples(): void
    {
        $rule = $this->createRule();

        $this->resetDomainEvents();

        $newExamples = RuleExamples::fromArray([RuleExample::good('new code')]);
        $rule->update(DirectiveVersion::initial(), examples: $newExamples);

        self::assertCount(1, $rule->examples);

        $this->assertDomainEventRecorded(DirectiveUpdated::class);
    }

    public function testItShouldUpdateMultiplePropertiesAtOnce(): void
    {
        $rule = $this->createRule();

        $this->resetDomainEvents();

        $newDescription = RuleDescription::fromString('New description');
        $newContent = RuleContent::fromString('New content');
        $newExamples = RuleExamples::fromArray([RuleExample::transformation('bad', 'good')]);

        $rule->update(
            DirectiveVersion::initial(),
            description: $newDescription,
            content: $newContent,
            examples: $newExamples,
        );

        self::assertSame($newDescription, $rule->description);
        self::assertSame($newContent, $rule->content);
        self::assertCount(1, $rule->examples);

        $this->assertDomainEventRecorded(DirectiveUpdated::class);
    }

    public function testItShouldNotChangePropertiesWhenUpdateIsEmpty(): void
    {
        $rule = $this->createRule();
        $originalDescription = $rule->description;
        $originalContent = $rule->content;

        $this->resetDomainEvents();

        $rule->update(DirectiveVersion::initial());

        self::assertSame($originalDescription, $rule->description);
        self::assertSame($originalContent, $rule->content);

        $this->assertDomainEventRecorded(DirectiveUpdated::class);
    }

    private function createRule(
        ?DirectiveId $id = null,
        ?DirectiveName $name = null,
        ?RuleDescription $description = null,
        ?RuleContent $content = null,
        ?RuleExamples $examples = null,
    ): Rule {
        return Rule::draft(
            $id ?? DirectiveId::fromString('my-rule'),
            $name ?? DirectiveName::fromString('my-rule-name'),
            $description ?? RuleDescription::fromString('Default description'),
            $content ?? RuleContent::fromString('Default content'),
            $examples,
        );
    }
}
