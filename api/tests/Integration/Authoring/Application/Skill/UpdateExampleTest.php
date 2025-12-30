<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\Authoring\Application\Skill;

use Dairectiv\Authoring\Application\Skill\UpdateExample\Input;
use Dairectiv\Authoring\Domain\Object\Directive\Event\DirectiveUpdated;
use Dairectiv\Authoring\Domain\Object\Directive\Exception\DirectiveNotFoundException;
use Dairectiv\Authoring\Domain\Object\Skill\Example\Example;
use Dairectiv\Authoring\Domain\Object\Skill\Skill;
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
        $skill = self::draftSkill();
        $example = Example::create($skill, 'Original scenario', 'Original input', 'Original output', 'Original explanation');
        $this->persistEntity($skill);

        $this->execute(new Input(
            (string) $skill->id,
            (string) $example->id,
            scenario: 'Updated scenario',
            input: 'Updated input',
            output: 'Updated output',
            explanation: 'Updated explanation',
        ));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $persistedSkill = $this->findEntity(Skill::class, ['id' => $skill->id], true);
        $persistedExample = $persistedSkill->examples->first();

        self::assertSame('Updated scenario', $persistedExample->scenario);
        self::assertSame('Updated input', $persistedExample->input);
        self::assertSame('Updated output', $persistedExample->output);
        self::assertSame('Updated explanation', $persistedExample->explanation);
    }

    public function testItShouldUpdateScenarioOnly(): void
    {
        $skill = self::draftSkill();
        $example = Example::create($skill, 'Original scenario', 'Original input', 'Original output', 'Original explanation');
        $this->persistEntity($skill);

        $this->execute(new Input(
            (string) $skill->id,
            (string) $example->id,
            scenario: 'Updated scenario',
        ));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $persistedSkill = $this->findEntity(Skill::class, ['id' => $skill->id], true);
        $persistedExample = $persistedSkill->examples->first();

        self::assertSame('Updated scenario', $persistedExample->scenario);
        self::assertSame('Original input', $persistedExample->input);
        self::assertSame('Original output', $persistedExample->output);
        self::assertSame('Original explanation', $persistedExample->explanation);
    }

    public function testItShouldUpdateInputOnly(): void
    {
        $skill = self::draftSkill();
        $example = Example::create($skill, 'Original scenario', 'Original input', 'Original output');
        $this->persistEntity($skill);

        $this->execute(new Input(
            (string) $skill->id,
            (string) $example->id,
            input: 'Updated input',
        ));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $persistedSkill = $this->findEntity(Skill::class, ['id' => $skill->id], true);
        $persistedExample = $persistedSkill->examples->first();

        self::assertSame('Original scenario', $persistedExample->scenario);
        self::assertSame('Updated input', $persistedExample->input);
    }

    public function testItShouldUpdateOutputOnly(): void
    {
        $skill = self::draftSkill();
        $example = Example::create($skill, 'Original scenario', 'Original input', 'Original output');
        $this->persistEntity($skill);

        $this->execute(new Input(
            (string) $skill->id,
            (string) $example->id,
            output: 'Updated output',
        ));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $persistedSkill = $this->findEntity(Skill::class, ['id' => $skill->id], true);
        $persistedExample = $persistedSkill->examples->first();

        self::assertSame('Original scenario', $persistedExample->scenario);
        self::assertSame('Updated output', $persistedExample->output);
    }

    public function testItShouldUpdateExplanationOnly(): void
    {
        $skill = self::draftSkill();
        $example = Example::create($skill, 'Original scenario', 'Original input', 'Original output');
        $this->persistEntity($skill);

        $this->execute(new Input(
            (string) $skill->id,
            (string) $example->id,
            explanation: 'New explanation',
        ));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $persistedSkill = $this->findEntity(Skill::class, ['id' => $skill->id], true);
        $persistedExample = $persistedSkill->examples->first();

        self::assertSame('New explanation', $persistedExample->explanation);
    }

    public function testItShouldThrowExceptionWhenSkillNotFound(): void
    {
        $this->expectException(DirectiveNotFoundException::class);

        $this->execute(new Input(
            'non-existent-skill',
            'non-existent-example',
            scenario: 'Updated',
        ));
    }

    public function testItShouldThrowExceptionWhenExampleNotFound(): void
    {
        $skill = self::draftSkill();
        $this->persistEntity($skill);

        $nonExistentId = '00000000-0000-0000-0000-000000000000';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(\sprintf('Example with ID "%s" not found.', $nonExistentId));

        $this->execute(new Input(
            (string) $skill->id,
            $nonExistentId,
            scenario: 'Updated',
        ));
    }

    public function testItShouldThrowExceptionWhenNoFieldsProvided(): void
    {
        $skill = self::draftSkill();
        $example = Example::create($skill, 'Scenario', 'Input', 'Output');
        $this->persistEntity($skill);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('At least one field must be provided.');

        $this->execute(new Input((string) $skill->id, (string) $example->id));
    }

    public function testItShouldThrowExceptionWhenSkillIsArchived(): void
    {
        $skill = self::draftSkill();
        $example = Example::create($skill, 'Scenario', 'Input', 'Output');
        $skill->archive();
        $this->persistEntity($skill);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot perform this action on an archived directive.');

        $this->execute(new Input(
            (string) $skill->id,
            (string) $example->id,
            scenario: 'Updated',
        ));
    }
}
