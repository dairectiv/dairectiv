<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\Authoring\Application\Skill;

use Dairectiv\Authoring\Application\Skill\Draft\Input;
use Dairectiv\Authoring\Application\Skill\Draft\Output;
use Dairectiv\Authoring\Domain\Object\Directive\DirectiveState;
use Dairectiv\Authoring\Domain\Object\Directive\Event\DirectiveDrafted;
use Dairectiv\Authoring\Domain\Object\Directive\Exception\DirectiveAlreadyExistsException;
use Dairectiv\Authoring\Domain\Object\Skill\Skill;
use Dairectiv\Tests\Framework\IntegrationTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

#[Group('integration')]
#[Group('authoring')]
#[Group('use-case')]
final class DraftTest extends IntegrationTestCase
{
    public function testItShouldDraftSkill(): void
    {
        $output = $this->executeDraftSkill();

        self::assertDomainEventHasBeenDispatched(DirectiveDrafted::class);

        $skill = $output->skill;

        self::assertSame('my-skill', (string) $skill->id);
        self::assertSame('My Skill', $skill->name);
        self::assertSame('A description of my skill', $skill->description);
        self::assertSame(DirectiveState::Draft, $skill->state);
        self::assertTrue($skill->examples->isEmpty());
        self::assertTrue($skill->steps->isEmpty());
        self::assertNull($skill->content);

        $persistedSkill = $this->findEntity(Skill::class, ['id' => $skill->id], true);

        self::assertSame('my-skill', (string) $persistedSkill->id);
        self::assertSame('My Skill', $persistedSkill->name);
        self::assertSame('A description of my skill', $persistedSkill->description);
        self::assertSame(DirectiveState::Draft, $persistedSkill->state);
        self::assertSame(DirectiveState::Draft, $persistedSkill->state);
        self::assertTrue($persistedSkill->examples->isEmpty());
        self::assertTrue($persistedSkill->steps->isEmpty());
        self::assertNull($persistedSkill->content);
    }

    public function testItShouldThrowExceptionWhenSkillAlreadyExists(): void
    {
        $skill = self::draftSkill(id: 'my-skill');
        $this->persistEntity($skill);

        $this->expectException(DirectiveAlreadyExistsException::class);

        $this->executeDraftSkill();
    }

    /**
     * @return iterable<string, array{name: string, expectedId: string}>
     */
    public static function provideNameToIdConversions(): iterable
    {
        yield 'spaces' => ['name' => 'My Awesome Skill', 'expectedId' => 'my-awesome-skill'];
        yield 'uppercase' => ['name' => 'UPPERCASE SKILL', 'expectedId' => 'uppercaseskill'];
        yield 'mixed case' => ['name' => 'MixedCase Skill Name', 'expectedId' => 'mixed-case-skill-name'];
        yield 'already kebab' => ['name' => 'already-kebab-case', 'expectedId' => 'already-kebab-case'];
        yield 'single word' => ['name' => 'Single', 'expectedId' => 'single'];
        yield 'camelCase' => ['name' => 'camelCaseSkill', 'expectedId' => 'camel-case-skill'];
        yield 'PascalCase' => ['name' => 'PascalCaseSkill', 'expectedId' => 'pascal-case-skill'];
        yield 'with numbers' => ['name' => 'Skill Version 2', 'expectedId' => 'skill-version2'];
    }

    #[DataProvider('provideNameToIdConversions')]
    public function testItShouldGenerateKebabCaseIdFromName(string $name, string $expectedId): void
    {
        $output = $this->executeDraftSkill($name);

        self::assertSame($expectedId, (string) $output->skill->id);
        self::assertDomainEventHasBeenDispatched(DirectiveDrafted::class);
    }

    public function testItShouldDraftMultipleDistinctSkills(): void
    {
        $output1 = $this->executeDraftSkill('First Skill', 'First description');
        $output2 = $this->executeDraftSkill('Second Skill', 'Second description');
        $output3 = $this->executeDraftSkill('Third Skill', 'Third description');

        self::assertSame('first-skill', (string) $output1->skill->id);
        self::assertSame('second-skill', (string) $output2->skill->id);
        self::assertSame('third-skill', (string) $output3->skill->id);

        // Verify all are persisted
        $persistedSkill1 = $this->findEntity(Skill::class, ['id' => $output1->skill->id], true);
        $persistedSkill2 = $this->findEntity(Skill::class, ['id' => $output2->skill->id], true);
        $persistedSkill3 = $this->findEntity(Skill::class, ['id' => $output3->skill->id], true);

        self::assertSame('First Skill', $persistedSkill1->name);
        self::assertSame('Second Skill', $persistedSkill2->name);
        self::assertSame('Third Skill', $persistedSkill3->name);

        self::assertDomainEventHasBeenDispatched(DirectiveDrafted::class, 3);
    }

    public function testItShouldPreserveOriginalNameAndDescription(): void
    {
        $output = $this->executeDraftSkill('  Skill With  Extra   Spaces  ', 'Description with trailing space ');

        // ID should be kebab-cased from trimmed name
        self::assertSame('skill-with-extra-spaces', (string) $output->skill->id);

        // But original name and description should be preserved as provided
        self::assertSame('  Skill With  Extra   Spaces  ', $output->skill->name);
        self::assertSame('Description with trailing space ', $output->skill->description);

        self::assertDomainEventHasBeenDispatched(DirectiveDrafted::class);
    }

    private function executeDraftSkill(
        string $name = 'My Skill',
        string $description = 'A description of my skill',
    ): Output {
        $output = $this->execute(new Input($name, $description));

        self::assertInstanceOf(Output::class, $output);

        return $output;
    }
}
