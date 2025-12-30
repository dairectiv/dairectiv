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
use Dairectiv\Authoring\Domain\Object\Rule\Example\Example;
use Dairectiv\Authoring\Domain\Object\Rule\Rule;
use Dairectiv\SharedKernel\Domain\Object\Exception\InvalidArgumentException;
use Dairectiv\Tests\Framework\UnitTestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('authoring')]
#[Group('unit')]
final class RuleTest extends UnitTestCase
{
    // ========================================================================
    // RULE LIFECYCLE
    // ========================================================================

    public function testItShouldDraftRule(): void
    {
        $id = DirectiveId::fromString('my-rule');

        $rule = Rule::draft($id, 'My Rule', 'A description of my rule');

        self::assertSame($id, $rule->id);
        self::assertSame('my-rule', (string) $rule->id);
        self::assertSame('My Rule', $rule->name);
        self::assertSame('A description of my rule', $rule->description);
        self::assertSame(DirectiveState::Draft, $rule->state);
        self::assertTrue($rule->examples->isEmpty());
        self::assertEquals(Chronos::now(), $rule->createdAt);
        self::assertEquals(Chronos::now(), $rule->updatedAt);

        $this->assertDomainEventRecorded(DirectiveDrafted::class);
    }

    public function testItShouldUpdateMetadata(): void
    {
        $rule = Rule::draft(DirectiveId::fromString('my-rule'), 'Original Name', 'Original Description');
        $this->resetDomainEvents();

        Chronos::setTestNow(Chronos::now()->addMinutes(1));
        $rule->updateMetadata('Updated Name', 'Updated Description');

        self::assertSame('Updated Name', $rule->name);
        self::assertSame('Updated Description', $rule->description);
        self::assertEquals(Chronos::now(), $rule->updatedAt);

        $this->assertDomainEventRecorded(DirectiveUpdated::class);
    }

    public function testItShouldPublishRule(): void
    {
        $rule = Rule::draft(DirectiveId::fromString('my-rule'), 'My Rule', 'Description');
        $this->resetDomainEvents();

        Chronos::setTestNow(Chronos::now()->addMinutes(1));
        $rule->publish();

        self::assertSame(DirectiveState::Published, $rule->state);
        self::assertEquals(Chronos::now(), $rule->updatedAt);

        $this->assertDomainEventRecorded(DirectivePublished::class);
    }

    public function testItShouldArchiveRule(): void
    {
        $rule = Rule::draft(DirectiveId::fromString('my-rule'), 'My Rule', 'Description');
        $rule->publish();
        $this->resetDomainEvents();

        Chronos::setTestNow(Chronos::now()->addMinutes(1));
        $rule->archive();

        self::assertSame(DirectiveState::Archived, $rule->state);
        self::assertEquals(Chronos::now(), $rule->updatedAt);

        $this->assertDomainEventRecorded(DirectiveArchived::class);
    }

    // ========================================================================
    // RULE LIFECYCLE - INVALID STATE TRANSITIONS
    // ========================================================================

    public function testItShouldNotPublishAlreadyPublishedRule(): void
    {
        $rule = Rule::draft(DirectiveId::fromString('my-rule'), 'My Rule', 'Description');
        $rule->publish();
        $this->resetDomainEvents();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Only draft directives can be published.');

        $rule->publish();
    }

    public function testItShouldNotPublishArchivedRule(): void
    {
        $rule = Rule::draft(DirectiveId::fromString('my-rule'), 'My Rule', 'Description');
        $rule->archive();
        $this->resetDomainEvents();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Only draft directives can be published.');

        $rule->publish();
    }

    public function testItShouldNotArchiveAlreadyArchivedRule(): void
    {
        $rule = Rule::draft(DirectiveId::fromString('my-rule'), 'My Rule', 'Description');
        $rule->archive();
        $this->resetDomainEvents();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Directive is already archived.');

        $rule->archive();
    }

    public function testItShouldNotUpdateMetadataOfArchivedRule(): void
    {
        $rule = Rule::draft(DirectiveId::fromString('my-rule'), 'My Rule', 'Description');
        $rule->archive();
        $this->resetDomainEvents();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot perform this action on an archived directive.');

        $rule->updateMetadata('New Name', 'New Description');
    }

    public function testItShouldNotUpdateMetadataWithNullFields(): void
    {
        $rule = Rule::draft(DirectiveId::fromString('my-rule'), 'My Rule', 'Description');
        $this->resetDomainEvents();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('At least one metadata field must be provided.');

        $rule->updateMetadata(null, null);
    }

    // ========================================================================
    // EXAMPLE MANAGEMENT
    // ========================================================================

    public function testItShouldAddExample(): void
    {
        $rule = Rule::draft(DirectiveId::fromString('my-rule'), 'My Rule', 'Description');
        $this->resetDomainEvents();

        Chronos::setTestNow(Chronos::now()->addMinutes(1));

        $rule->addExample('Good example', 'Bad example', 'Explanation');

        self::assertCount(1, $rule->examples);
        $example = $rule->examples->first();
        self::assertInstanceOf(Example::class, $example);
        self::assertSame('Good example', $example->good);
        self::assertSame('Bad example', $example->bad);
        self::assertSame('Explanation', $example->explanation);
        self::assertSame($rule, $example->rule);
        self::assertEquals(Chronos::now(), $example->createdAt);
        self::assertEquals(Chronos::now(), $example->updatedAt);
        self::assertEquals(Chronos::now(), $rule->updatedAt);

        $this->assertDomainEventRecorded(DirectiveUpdated::class);
    }

    public function testItShouldAddExampleWithOnlyGood(): void
    {
        $rule = Rule::draft(DirectiveId::fromString('my-rule'), 'My Rule', 'Description');
        $this->resetDomainEvents();

        $rule->addExample('Good example', null, null);

        self::assertCount(1, $rule->examples);
        $example = $rule->examples->first();
        self::assertInstanceOf(Example::class, $example);
        self::assertSame('Good example', $example->good);
        self::assertNull($example->bad);
        self::assertNull($example->explanation);

        $this->assertDomainEventRecorded(DirectiveUpdated::class);
    }

    public function testItShouldAddExampleWithOnlyBad(): void
    {
        $rule = Rule::draft(DirectiveId::fromString('my-rule'), 'My Rule', 'Description');
        $this->resetDomainEvents();

        $rule->addExample(null, 'Bad example', null);

        self::assertCount(1, $rule->examples);
        $example = $rule->examples->first();
        self::assertInstanceOf(Example::class, $example);
        self::assertNull($example->good);
        self::assertSame('Bad example', $example->bad);
        self::assertNull($example->explanation);

        $this->assertDomainEventRecorded(DirectiveUpdated::class);
    }

    public function testItShouldAddMultipleExamples(): void
    {
        $rule = Rule::draft(DirectiveId::fromString('my-rule'), 'My Rule', 'Description');
        $this->resetDomainEvents();

        $rule->addExample('Good 1', 'Bad 1', 'Explanation 1');
        $rule->addExample('Good 2', 'Bad 2', 'Explanation 2');
        $rule->addExample('Good 3', 'Bad 3', 'Explanation 3');

        self::assertCount(3, $rule->examples);

        $this->assertDomainEventRecorded(DirectiveUpdated::class, 3);
    }

    public function testItShouldNotAddExampleToArchivedRule(): void
    {
        $rule = Rule::draft(DirectiveId::fromString('my-rule'), 'My Rule', 'Description');
        $rule->archive();
        $this->resetDomainEvents();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot perform this action on an archived directive.');

        $rule->addExample('Good', 'Bad', 'Explanation');
    }
}
