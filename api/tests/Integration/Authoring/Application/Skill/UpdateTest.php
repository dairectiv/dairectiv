<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\Authoring\Application\Skill;

use Dairectiv\Authoring\Application\Skill\Update\Input;
use Dairectiv\Authoring\Domain\Object\Directive\DirectiveState;
use Dairectiv\Authoring\Domain\Object\Directive\Event\DirectiveUpdated;
use Dairectiv\Authoring\Domain\Object\Directive\Exception\DirectiveNotFoundException;
use Dairectiv\Authoring\Domain\Object\Skill\Skill;
use Dairectiv\SharedKernel\Domain\Object\Exception\InvalidArgumentException;
use Dairectiv\Tests\Framework\IntegrationTestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('integration')]
#[Group('authoring')]
#[Group('use-case')]
final class UpdateTest extends IntegrationTestCase
{
    public function testItShouldUpdateSkillMetadata(): void
    {
        $skill = self::draftSkill();
        $this->persistEntity($skill);

        $this->execute(new Input(
            (string) $skill->id,
            name: 'Updated Name',
            description: 'Updated Description',
        ));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $persistedSkill = $this->findEntity(Skill::class, ['id' => $skill->id], true);

        self::assertSame('Updated Name', $persistedSkill->name);
        self::assertSame('Updated Description', $persistedSkill->description);
        self::assertSame(DirectiveState::Draft, $persistedSkill->state);
    }

    public function testItShouldUpdateSkillNameOnly(): void
    {
        $skill = self::draftSkill(name: 'Original Name', description: 'Original Description');
        $this->persistEntity($skill);

        $this->execute(new Input(
            (string) $skill->id,
            name: 'New Name',
        ));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $persistedSkill = $this->findEntity(Skill::class, ['id' => $skill->id], true);

        self::assertSame('New Name', $persistedSkill->name);
        self::assertSame('Original Description', $persistedSkill->description);
    }

    public function testItShouldUpdateSkillDescriptionOnly(): void
    {
        $skill = self::draftSkill(name: 'Original Name', description: 'Original Description');
        $this->persistEntity($skill);

        $this->execute(new Input(
            (string) $skill->id,
            description: 'New Description',
        ));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $persistedSkill = $this->findEntity(Skill::class, ['id' => $skill->id], true);

        self::assertSame('Original Name', $persistedSkill->name);
        self::assertSame('New Description', $persistedSkill->description);
    }

    public function testItShouldUpdateSkillContentOnly(): void
    {
        $skill = self::draftSkill();
        $this->persistEntity($skill);

        $this->execute(new Input(
            (string) $skill->id,
            content: 'New skill content',
        ));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $persistedSkill = $this->findEntity(Skill::class, ['id' => $skill->id], true);

        self::assertSame('New skill content', $persistedSkill->content);
    }

    public function testItShouldUpdateAllFields(): void
    {
        $skill = self::draftSkill(name: 'Original Name', description: 'Original Description');
        $this->persistEntity($skill);

        $this->execute(new Input(
            (string) $skill->id,
            name: 'New Name',
            description: 'New Description',
            content: 'New Content',
        ));

        // 2 events: one for metadata, one for content
        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class, 2);

        $persistedSkill = $this->findEntity(Skill::class, ['id' => $skill->id], true);

        self::assertSame('New Name', $persistedSkill->name);
        self::assertSame('New Description', $persistedSkill->description);
        self::assertSame('New Content', $persistedSkill->content);
    }

    public function testItShouldThrowExceptionWhenSkillNotFound(): void
    {
        $this->expectException(DirectiveNotFoundException::class);

        $this->execute(new Input(
            'non-existent-skill',
            name: 'New Name',
        ));
    }

    public function testItShouldThrowExceptionWhenSkillIsArchived(): void
    {
        $skill = self::draftSkill();
        $skill->archive();
        $this->persistEntity($skill);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot perform this action on an archived directive.');

        $this->execute(new Input(
            (string) $skill->id,
            name: 'New Name',
        ));
    }

    public function testItShouldThrowExceptionWhenNoFieldsProvided(): void
    {
        $skill = self::draftSkill();
        $this->persistEntity($skill);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('At least one field must be provided.');

        $this->execute(new Input((string) $skill->id));
    }

    public function testItShouldUpdatePublishedSkill(): void
    {
        $skill = self::draftSkill();
        $skill->publish();
        $this->persistEntity($skill);

        $this->execute(new Input(
            (string) $skill->id,
            name: 'Updated Published Skill',
        ));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $persistedSkill = $this->findEntity(Skill::class, ['id' => $skill->id], true);

        self::assertSame('Updated Published Skill', $persistedSkill->name);
        self::assertSame(DirectiveState::Published, $persistedSkill->state);
    }
}
