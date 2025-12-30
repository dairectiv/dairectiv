<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\Authoring\Application\Skill;

use Dairectiv\Authoring\Application\Skill\RemoveExample\Input;
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
final class RemoveExampleTest extends IntegrationTestCase
{
    public function testItShouldRemoveExampleFromSkill(): void
    {
        $skill = self::draftSkill();
        $example = Example::create($skill, 'Scenario', 'Input', 'Output', 'Explanation');
        $this->persistEntity($skill);

        self::assertCount(1, $skill->examples);

        $this->execute(new Input((string) $skill->id, (string) $example->id));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $persistedSkill = $this->findEntity(Skill::class, ['id' => $skill->id], true);

        self::assertCount(0, $persistedSkill->examples);
    }

    public function testItShouldRemoveOneExampleFromMultiple(): void
    {
        $skill = self::draftSkill();
        $example1 = Example::create($skill, 'Scenario 1', 'Input 1', 'Output 1');
        $example2 = Example::create($skill, 'Scenario 2', 'Input 2', 'Output 2');
        $example3 = Example::create($skill, 'Scenario 3', 'Input 3', 'Output 3');
        $this->persistEntity($skill);

        self::assertCount(3, $skill->examples);

        $this->execute(new Input((string) $skill->id, (string) $example2->id));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $persistedSkill = $this->findEntity(Skill::class, ['id' => $skill->id], true);

        self::assertCount(2, $persistedSkill->examples);

        $remainingScenarios = $persistedSkill->examples->map(fn ($e) => $e->scenario)->toArray();
        self::assertContains('Scenario 1', $remainingScenarios);
        self::assertContains('Scenario 3', $remainingScenarios);
        self::assertNotContains('Scenario 2', $remainingScenarios);
    }

    public function testItShouldThrowExceptionWhenSkillNotFound(): void
    {
        $this->expectException(DirectiveNotFoundException::class);

        $this->execute(new Input('non-existent-skill', '00000000-0000-0000-0000-000000000000'));
    }

    public function testItShouldThrowExceptionWhenExampleNotFound(): void
    {
        $skill = self::draftSkill();
        $this->persistEntity($skill);

        $nonExistentId = '00000000-0000-0000-0000-000000000000';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(\sprintf('Example with ID "%s" not found.', $nonExistentId));

        $this->execute(new Input((string) $skill->id, $nonExistentId));
    }

    public function testItShouldThrowExceptionWhenSkillIsArchived(): void
    {
        $skill = self::draftSkill();
        $example = Example::create($skill, 'Scenario', 'Input', 'Output');
        $skill->archive();
        $this->persistEntity($skill);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot perform this action on an archived directive.');

        $this->execute(new Input((string) $skill->id, (string) $example->id));
    }
}
