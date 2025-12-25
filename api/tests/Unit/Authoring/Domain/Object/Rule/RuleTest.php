<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Unit\Authoring\Domain\Object\Rule;

use Cake\Chronos\Chronos;
use Dairectiv\Authoring\Domain\Object\Directive\DirectiveId;
use Dairectiv\Authoring\Domain\Object\Directive\DirectiveState;
use Dairectiv\Authoring\Domain\Object\Directive\Event\DirectiveArchived;
use Dairectiv\Authoring\Domain\Object\Directive\Event\DirectiveDrafted;
use Dairectiv\Authoring\Domain\Object\Directive\Event\DirectivePublished;
use Dairectiv\Authoring\Domain\Object\Directive\Event\DirectiveUpdated;
use Dairectiv\Authoring\Domain\Object\Directive\Metadata\DirectiveDescription;
use Dairectiv\Authoring\Domain\Object\Directive\Metadata\DirectiveMetadata;
use Dairectiv\Authoring\Domain\Object\Directive\Metadata\DirectiveName;
use Dairectiv\Authoring\Domain\Object\Rule\Rule;
use Dairectiv\Authoring\Domain\Object\Rule\RuleContent;
use Dairectiv\Authoring\Domain\Object\Rule\RuleExample;
use Dairectiv\Authoring\Domain\Object\Rule\RuleExamples;
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
        self::assertSame($name, $rule->metadata->name);
        self::assertSame('my-rule-name', (string) $rule->metadata->name);
        self::assertSame(DirectiveState::Draft, $rule->state);
        self::assertSame(1, $rule->getCurrentVersion()->number->number);

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

    public function testItShouldIncrementVersionWhenUpdatingContent(): void
    {
        $id = DirectiveId::fromString('my-rule');
        $name = DirectiveName::fromString('my-rule-name');
        $rule = $this->createRule($id, $name);

        $this->resetDomainEvents();

        $rule->updateContent();

        self::assertSame(2, $rule->getCurrentVersion()->number->number);

        $this->assertDomainEventRecorded(DirectiveUpdated::class);
    }

    public function testItShouldUpdateUpdatedAtWhenUpdatingContent(): void
    {
        $id = DirectiveId::fromString('my-rule');
        $name = DirectiveName::fromString('my-rule-name');
        $rule = $this->createRule($id, $name);

        $initialUpdatedAt = $rule->updatedAt;

        Chronos::setTestNow(Chronos::now()->addMinutes(5));
        $this->resetDomainEvents();

        $rule->updateContent();

        self::assertFalse($rule->updatedAt->equals($initialUpdatedAt));
        self::assertTrue($rule->updatedAt->greaterThan($initialUpdatedAt));

        $this->assertDomainEventRecorded(DirectiveUpdated::class);
    }

    public function testItShouldRecordDirectiveUpdatedEventWhenUpdatingContent(): void
    {
        $id = DirectiveId::fromString('my-rule');
        $name = DirectiveName::fromString('my-rule-name');
        $rule = $this->createRule($id, $name);

        $this->resetDomainEvents();

        $rule->updateContent();

        $event = $this->assertDomainEventRecorded(DirectiveUpdated::class);

        self::assertSame($id, $event->directiveId);
        self::assertSame(2, $event->versionNumber->number);
    }

    public function testItShouldAllowMultipleContentUpdates(): void
    {
        $id = DirectiveId::fromString('my-rule');
        $name = DirectiveName::fromString('my-rule-name');
        $rule = $this->createRule($id, $name);

        $this->resetDomainEvents();

        $rule->updateContent();
        $rule->updateContent();
        $rule->updateContent();

        self::assertSame(4, $rule->getCurrentVersion()->number->number);

        $this->assertDomainEventRecorded(DirectiveUpdated::class);
        $this->assertDomainEventRecorded(DirectiveUpdated::class);
        $this->assertDomainEventRecorded(DirectiveUpdated::class);
    }

    public function testItShouldNotIncrementVersionWhenUpdatingMetadata(): void
    {
        $rule = $this->createRule();
        $initialVersion = $rule->getCurrentVersion();

        $this->resetDomainEvents();

        $rule->updateMetadata(
            name: DirectiveName::fromString('new-name'),
            description: DirectiveDescription::fromString('New description'),
        );

        self::assertSame($initialVersion->number->number, $rule->getCurrentVersion()->number->number);
        self::assertSame('new-name', (string) $rule->metadata->name);
        self::assertSame('New description', (string) $rule->metadata->description);
    }

    public function testItShouldUpdateUpdatedAtWhenUpdatingMetadata(): void
    {
        $rule = $this->createRule();
        $initialUpdatedAt = $rule->updatedAt;

        $this->resetDomainEvents();

        Chronos::setTestNow(Chronos::now()->addMinutes(5));

        $rule->updateMetadata(name: DirectiveName::fromString('new-name'));

        self::assertFalse($rule->updatedAt->equals($initialUpdatedAt));
        self::assertTrue($rule->updatedAt->greaterThan($initialUpdatedAt));

        $this->assertNoDomainEvents();
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

        $initialVersion = $rule->getCurrentVersion();

        $this->resetDomainEvents();

        $rule->publish();

        self::assertSame($initialVersion, $rule->getCurrentVersion());

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

        $initialVersion = $rule->getCurrentVersion();

        $this->resetDomainEvents();

        $rule->archive();

        self::assertSame($initialVersion, $rule->getCurrentVersion());

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

        $rule->updateContent();
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
        $description = DirectiveDescription::fromString('A rule description');
        $content = RuleContent::fromString('## MUST\n- Use sprintf');

        $rule = Rule::draft($id, DirectiveMetadata::create($name, $description), $content);

        self::assertSame($description, $rule->metadata->description);
        self::assertSame($content, $rule->content);
        self::assertTrue($rule->examples->isEmpty());

        $this->assertDomainEventRecorded(DirectiveDrafted::class);
    }

    public function testItShouldCreateRuleWithExamples(): void
    {
        $id = DirectiveId::fromString('my-rule');
        $name = DirectiveName::fromString('my-rule-name');
        $description = DirectiveDescription::fromString('A rule description');
        $content = RuleContent::fromString('## MUST\n- Use sprintf');
        $examples = RuleExamples::fromList([
            RuleExample::good('sprintf code'),
            RuleExample::bad('interpolation code'),
        ]);

        $rule = Rule::draft($id, DirectiveMetadata::create($name, $description), $content, $examples);

        self::assertCount(2, $rule->examples);

        $this->assertDomainEventRecorded(DirectiveDrafted::class);
    }

    public function testItShouldUpdateDescriptionViaMetadata(): void
    {
        $rule = $this->createRule();

        $this->resetDomainEvents();

        $newDescription = DirectiveDescription::fromString('Updated description');
        $rule->updateMetadata(description: $newDescription);

        self::assertSame($newDescription, $rule->metadata->description);

        $this->assertNoDomainEvents();
    }

    public function testItShouldUpdateContent(): void
    {
        $rule = $this->createRule();

        $this->resetDomainEvents();

        $newContent = RuleContent::fromString('Updated content');
        $rule->updateContent(content: $newContent);

        self::assertSame($newContent, $rule->content);

        $this->assertDomainEventRecorded(DirectiveUpdated::class);
    }

    public function testItShouldUpdateExamples(): void
    {
        $rule = $this->createRule();

        $this->resetDomainEvents();

        $newExamples = RuleExamples::fromList([RuleExample::good('new code')]);
        $rule->updateContent(examples: $newExamples);

        self::assertCount(1, $rule->examples);

        $this->assertDomainEventRecorded(DirectiveUpdated::class);
    }

    public function testItShouldUpdateContentAndExamplesAtOnce(): void
    {
        $rule = $this->createRule();

        $this->resetDomainEvents();

        $newContent = RuleContent::fromString('New content');
        $newExamples = RuleExamples::fromList([RuleExample::transformation('bad', 'good')]);

        $rule->updateContent(
            content: $newContent,
            examples: $newExamples,
        );

        self::assertSame($newContent, $rule->content);
        self::assertCount(1, $rule->examples);

        $this->assertDomainEventRecorded(DirectiveUpdated::class);
    }

    public function testItShouldNotChangePropertiesWhenContentUpdateIsEmpty(): void
    {
        $rule = $this->createRule();
        $originalContent = $rule->content;

        $this->resetDomainEvents();

        $rule->updateContent();

        self::assertSame($originalContent, $rule->content);

        $this->assertDomainEventRecorded(DirectiveUpdated::class);
    }

    private function createRule(
        ?DirectiveId $id = null,
        ?DirectiveName $name = null,
        ?DirectiveDescription $description = null,
        ?RuleContent $content = null,
        ?RuleExamples $examples = null,
    ): Rule {
        return Rule::draft(
            $id ?? DirectiveId::fromString('my-rule'),
            DirectiveMetadata::create(
                $name ?? DirectiveName::fromString('my-rule-name'),
                $description ?? DirectiveDescription::fromString('Default description'),
            ),
            $content ?? RuleContent::fromString('Default content'),
            $examples,
        );
    }
}
