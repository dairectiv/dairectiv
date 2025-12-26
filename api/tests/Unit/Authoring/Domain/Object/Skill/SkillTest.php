<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Unit\Authoring\Domain\Object\Skill;

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
use Dairectiv\Authoring\Domain\Object\Skill\Skill;
use Dairectiv\Authoring\Domain\Object\Skill\SkillContent;
use Dairectiv\Authoring\Domain\Object\Skill\SkillExample;
use Dairectiv\Authoring\Domain\Object\Skill\SkillExamples;
use Dairectiv\Authoring\Domain\Object\Skill\Workflow\SequentialWorkflow;
use Dairectiv\Authoring\Domain\Object\Skill\Workflow\SkillStep;
use Dairectiv\Authoring\Domain\Object\Skill\Workflow\SkillWorkflow;
use Dairectiv\Tests\Framework\AggregateRootAssertions;
use PHPUnit\Framework\TestCase;

final class SkillTest extends TestCase
{
    use AggregateRootAssertions;

    public function testItShouldCreateDraftSkillWithInitialState(): void
    {
        $id = DirectiveId::fromString('my-skill');
        $name = DirectiveName::fromString('my-skill-name');

        $skill = $this->createSkill($id, $name);

        self::assertSame($id, $skill->id);
        self::assertSame($name, $skill->metadata->name);
        self::assertSame('my-skill-name', (string) $skill->metadata->name);
        self::assertSame(DirectiveState::Draft, $skill->state);
        self::assertSame(1, $skill->getCurrentVersion()->number->number);

        $this->assertDomainEventRecorded(DirectiveDrafted::class);
    }

    public function testItShouldRecordDirectiveDraftedEventWhenCreating(): void
    {
        $id = DirectiveId::fromString('my-skill');

        $this->createSkill($id);

        $event = $this->assertDomainEventRecorded(DirectiveDrafted::class);

        self::assertSame($id, $event->directiveId);
    }

    public function testItShouldHaveSameTimestampForCreatedAtAndUpdatedAtWhenDrafting(): void
    {
        $id = DirectiveId::fromString('my-skill');
        $name = DirectiveName::fromString('my-skill-name');

        $skill = $this->createSkill($id, $name);

        self::assertTrue($skill->createdAt->equals($skill->updatedAt));

        $this->assertDomainEventRecorded(DirectiveDrafted::class);
    }

    public function testItShouldIncrementVersionWhenUpdatingContent(): void
    {
        $id = DirectiveId::fromString('my-skill');
        $name = DirectiveName::fromString('my-skill-name');
        $skill = $this->createSkill($id, $name);

        $this->resetDomainEvents();

        $skill->updateContent();

        self::assertSame(2, $skill->getCurrentVersion()->number->number);

        $this->assertDomainEventRecorded(DirectiveUpdated::class);
    }

    public function testItShouldUpdateUpdatedAtWhenUpdatingContent(): void
    {
        $id = DirectiveId::fromString('my-skill');
        $name = DirectiveName::fromString('my-skill-name');
        $skill = $this->createSkill($id, $name);

        $initialUpdatedAt = $skill->updatedAt;

        Chronos::setTestNow(Chronos::now()->addMinutes(5));
        $this->resetDomainEvents();

        $skill->updateContent();

        self::assertFalse($skill->updatedAt->equals($initialUpdatedAt));
        self::assertTrue($skill->updatedAt->greaterThan($initialUpdatedAt));

        $this->assertDomainEventRecorded(DirectiveUpdated::class);
    }

    public function testItShouldRecordDirectiveUpdatedEventWhenUpdatingContent(): void
    {
        $id = DirectiveId::fromString('my-skill');
        $name = DirectiveName::fromString('my-skill-name');
        $skill = $this->createSkill($id, $name);

        $this->resetDomainEvents();

        $skill->updateContent();

        $event = $this->assertDomainEventRecorded(DirectiveUpdated::class);

        self::assertSame($id, $event->directiveId);
        self::assertSame(2, $event->versionNumber->number);
    }

    public function testItShouldAllowMultipleContentUpdates(): void
    {
        $id = DirectiveId::fromString('my-skill');
        $name = DirectiveName::fromString('my-skill-name');
        $skill = $this->createSkill($id, $name);

        $this->resetDomainEvents();

        $skill->updateContent();
        $skill->updateContent();
        $skill->updateContent();

        self::assertSame(4, $skill->getCurrentVersion()->number->number);

        $this->assertDomainEventRecorded(DirectiveUpdated::class);
        $this->assertDomainEventRecorded(DirectiveUpdated::class);
        $this->assertDomainEventRecorded(DirectiveUpdated::class);
    }

    public function testItShouldNotIncrementVersionWhenUpdatingMetadata(): void
    {
        $skill = $this->createSkill();
        $initialVersion = $skill->getCurrentVersion();

        $this->resetDomainEvents();

        $skill->updateMetadata(
            name: DirectiveName::fromString('new-name'),
            description: DirectiveDescription::fromString('New description'),
        );

        self::assertSame($initialVersion->number->number, $skill->getCurrentVersion()->number->number);
        self::assertSame('new-name', (string) $skill->metadata->name);
        self::assertSame('New description', (string) $skill->metadata->description);
    }

    public function testItShouldChangeStateToPublishedWhenPublishing(): void
    {
        $id = DirectiveId::fromString('my-skill');
        $name = DirectiveName::fromString('my-skill-name');
        $skill = $this->createSkill($id, $name);

        $this->resetDomainEvents();

        $skill->publish();

        self::assertSame(DirectiveState::Published, $skill->state);

        $this->assertDomainEventRecorded(DirectivePublished::class);
    }

    public function testItShouldRecordDirectivePublishedEventWhenPublishing(): void
    {
        $id = DirectiveId::fromString('my-skill');
        $name = DirectiveName::fromString('my-skill-name');
        $skill = $this->createSkill($id, $name);

        $this->resetDomainEvents();

        $skill->publish();

        $event = $this->assertDomainEventRecorded(DirectivePublished::class);

        self::assertSame($id, $event->directiveId);
    }

    public function testItShouldChangeStateToArchivedWhenArchiving(): void
    {
        $id = DirectiveId::fromString('my-skill');
        $name = DirectiveName::fromString('my-skill-name');
        $skill = $this->createSkill($id, $name);

        $this->resetDomainEvents();

        $skill->archive();

        self::assertSame(DirectiveState::Archived, $skill->state);

        $this->assertDomainEventRecorded(DirectiveArchived::class);
    }

    public function testItShouldRecordDirectiveArchivedEventWhenArchiving(): void
    {
        $id = DirectiveId::fromString('my-skill');
        $name = DirectiveName::fromString('my-skill-name');
        $skill = $this->createSkill($id, $name);

        $this->resetDomainEvents();

        $skill->archive();

        $event = $this->assertDomainEventRecorded(DirectiveArchived::class);

        self::assertSame($id, $event->directiveId);
    }

    public function testItShouldCreateSkillWithDescriptionAndContent(): void
    {
        $id = DirectiveId::fromString('my-skill');
        $name = DirectiveName::fromString('my-skill-name');
        $description = DirectiveDescription::fromString('A skill description');
        $content = SkillContent::fromString('## When to Use\nUse this skill when...');
        $workflow = $this->createSequentialWorkflow();

        $skill = Skill::draft($id, DirectiveMetadata::create($name, $description), $content, $workflow);

        self::assertSame($description, $skill->metadata->description);
        self::assertSame($content, $skill->content);
        self::assertSame($workflow, $skill->workflow);
        self::assertTrue($skill->examples->isEmpty());

        $this->assertDomainEventRecorded(DirectiveDrafted::class);
    }

    public function testItShouldCreateSkillWithExamples(): void
    {
        $id = DirectiveId::fromString('my-skill');
        $name = DirectiveName::fromString('my-skill-name');
        $description = DirectiveDescription::fromString('A skill description');
        $content = SkillContent::fromString('## When to Use\nUse this skill when...');
        $workflow = $this->createSequentialWorkflow();
        $examples = SkillExamples::fromList([
            SkillExample::create('User asks to commit', 'git status output', 'feat: add new feature'),
        ]);

        $skill = Skill::draft($id, DirectiveMetadata::create($name, $description), $content, $workflow, $examples);

        self::assertCount(1, $skill->examples);

        $this->assertDomainEventRecorded(DirectiveDrafted::class);
    }

    public function testItShouldUpdateContent(): void
    {
        $skill = $this->createSkill();

        $this->resetDomainEvents();

        $newContent = SkillContent::fromString('Updated content');
        $skill->updateContent(content: $newContent);

        self::assertSame($newContent, $skill->content);

        $this->assertDomainEventRecorded(DirectiveUpdated::class);
    }

    public function testItShouldUpdateWorkflow(): void
    {
        $skill = $this->createSkill();

        $this->resetDomainEvents();

        $newWorkflow = SequentialWorkflow::create([
            SkillStep::action(1, 'New Step', 'New content'),
        ]);
        $skill->updateContent(workflow: $newWorkflow);

        self::assertSame($newWorkflow, $skill->workflow);

        $this->assertDomainEventRecorded(DirectiveUpdated::class);
    }

    public function testItShouldUpdateExamples(): void
    {
        $skill = $this->createSkill();

        $this->resetDomainEvents();

        $newExamples = SkillExamples::fromList([
            SkillExample::create('Scenario', 'input', 'output'),
        ]);
        $skill->updateContent(examples: $newExamples);

        self::assertCount(1, $skill->examples);

        $this->assertDomainEventRecorded(DirectiveUpdated::class);
    }

    public function testItShouldUpdateAllContentFieldsAtOnce(): void
    {
        $skill = $this->createSkill();

        $this->resetDomainEvents();

        $newContent = SkillContent::fromString('New content');
        $newWorkflow = SequentialWorkflow::create([
            SkillStep::action(1, 'New Step', 'New step content'),
        ]);
        $newExamples = SkillExamples::fromList([
            SkillExample::create('Scenario', 'input', 'output'),
        ]);

        $skill->updateContent(
            content: $newContent,
            workflow: $newWorkflow,
            examples: $newExamples,
        );

        self::assertSame($newContent, $skill->content);
        self::assertSame($newWorkflow, $skill->workflow);
        self::assertCount(1, $skill->examples);

        $this->assertDomainEventRecorded(DirectiveUpdated::class);
    }

    public function testItShouldReturnCurrentSnapshot(): void
    {
        $content = SkillContent::fromString('Skill content');
        $workflow = $this->createSequentialWorkflow();
        $examples = SkillExamples::fromList([
            SkillExample::create('Scenario', 'input', 'output'),
        ]);

        $skill = Skill::draft(
            DirectiveId::fromString('my-skill'),
            DirectiveMetadata::create(
                DirectiveName::fromString('my-skill'),
                DirectiveDescription::fromString('Description'),
            ),
            $content,
            $workflow,
            $examples,
        );

        $snapshot = $skill->getCurrentSnapshot();

        self::assertSame($content, $snapshot->content);
        self::assertSame($workflow, $snapshot->workflow);
        self::assertSame($examples, $snapshot->examples);

        $this->assertDomainEventRecorded(DirectiveDrafted::class);
    }

    private function createSkill(
        ?DirectiveId $id = null,
        ?DirectiveName $name = null,
        ?DirectiveDescription $description = null,
        ?SkillContent $content = null,
        ?SkillWorkflow $workflow = null,
        ?SkillExamples $examples = null,
    ): Skill {
        return Skill::draft(
            $id ?? DirectiveId::fromString('my-skill'),
            DirectiveMetadata::create(
                $name ?? DirectiveName::fromString('my-skill-name'),
                $description ?? DirectiveDescription::fromString('Default description'),
            ),
            $content ?? SkillContent::fromString('Default content'),
            $workflow ?? $this->createSequentialWorkflow(),
            $examples,
        );
    }

    private function createSequentialWorkflow(): SequentialWorkflow
    {
        return SequentialWorkflow::create([
            SkillStep::action(1, 'Step 1', 'Do this first'),
            SkillStep::validation(2, 'Step 2', 'Verify the result'),
        ]);
    }
}
