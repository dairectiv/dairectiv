<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\Authoring\Application\Skill;

use Cake\Chronos\Chronos;
use Dairectiv\Authoring\Application\Skill\UpdateStep\Input;
use Dairectiv\Authoring\Domain\Object\Directive\Event\DirectiveUpdated;
use Dairectiv\Authoring\Domain\Object\Directive\Exception\DirectiveNotFoundException;
use Dairectiv\Authoring\Domain\Object\Skill\Skill;
use Dairectiv\Authoring\Domain\Object\Skill\Workflow\Step;
use Dairectiv\SharedKernel\Domain\Object\Exception\InvalidArgumentException;
use Dairectiv\Tests\Framework\IntegrationTestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('integration')]
#[Group('authoring')]
#[Group('use-case')]
final class UpdateStepTest extends IntegrationTestCase
{
    public function testItShouldUpdateStepContent(): void
    {
        $skill = self::draftSkill();
        $step = Step::create($skill, 'Original content');
        $this->persistEntity($skill);

        self::assertSame('Original content', $step->content);

        $this->execute(new Input(
            (string) $skill->id,
            (string) $step->id,
            'Updated content',
        ));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $persistedSkill = $this->findEntity(Skill::class, ['id' => $skill->id], true);
        $persistedStep = $persistedSkill->steps->first();

        self::assertInstanceOf(Step::class, $persistedStep);

        self::assertSame('Updated content', $persistedStep->content);
    }

    public function testItShouldUpdateStepTimestamp(): void
    {
        $skill = self::draftSkill();
        $step = Step::create($skill, 'Content');
        $this->persistEntity($skill);

        Chronos::setTestNow(Chronos::now()->addDays(1));

        $this->execute(new Input(
            (string) $skill->id,
            (string) $step->id,
            'New content',
        ));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $persistedSkill = $this->findEntity(Skill::class, ['id' => $skill->id], true);
        $persistedStep = $persistedSkill->steps->first();

        self::assertInstanceOf(Step::class, $persistedStep);
        self::assertTrue($persistedStep->updatedAt->greaterThan($persistedStep->createdAt));
    }

    public function testItShouldPreserveStepOrder(): void
    {
        $skill = self::draftSkill();
        $step1 = Step::create($skill, 'Step 1');
        $step2 = Step::create($skill, 'Step 2', $step1);
        Step::create($skill, 'Step 3', $step2);
        $this->persistEntity($skill);

        $this->execute(new Input(
            (string) $skill->id,
            (string) $step2->id,
            'Updated Step 2',
        ));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $persistedSkill = $this->findEntity(Skill::class, ['id' => $skill->id], true);

        $stepsOrdered = $persistedSkill->steps->toArray();
        self::assertSame('Step 1', $stepsOrdered[0]->content);
        self::assertSame(1, $stepsOrdered[0]->order);
        self::assertSame('Updated Step 2', $stepsOrdered[1]->content);
        self::assertSame(2, $stepsOrdered[1]->order);
        self::assertSame('Step 3', $stepsOrdered[2]->content);
        self::assertSame(3, $stepsOrdered[2]->order);
    }

    public function testItShouldThrowExceptionWhenSkillNotFound(): void
    {
        $this->expectException(DirectiveNotFoundException::class);

        $this->execute(new Input(
            'non-existent-skill',
            '00000000-0000-0000-0000-000000000000',
            'Content',
        ));
    }

    public function testItShouldThrowExceptionWhenStepNotFound(): void
    {
        $skill = self::draftSkill();
        $this->persistEntity($skill);

        $nonExistentId = '00000000-0000-0000-0000-000000000000';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(\sprintf('Step with ID "%s" not found.', $nonExistentId));

        $this->execute(new Input((string) $skill->id, $nonExistentId, 'Content'));
    }

    public function testItShouldThrowExceptionWhenSkillIsArchived(): void
    {
        $skill = self::draftSkill();
        $step = Step::create($skill, 'Content');
        $skill->archive();
        $this->persistEntity($skill);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot perform this action on an archived directive.');

        $this->execute(new Input((string) $skill->id, (string) $step->id, 'New content'));
    }

    public function testItShouldUpdateOnlySpecifiedStep(): void
    {
        $skill = self::draftSkill();
        $step1 = Step::create($skill, 'Original Step 1');
        Step::create($skill, 'Original Step 2', $step1);
        $this->persistEntity($skill);

        $this->execute(new Input(
            (string) $skill->id,
            (string) $step1->id,
            'Updated Step 1',
        ));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $persistedSkill = $this->findEntity(Skill::class, ['id' => $skill->id], true);
        $stepsOrdered = $persistedSkill->steps->toArray();

        self::assertSame('Updated Step 1', $stepsOrdered[0]->content);
        self::assertSame('Original Step 2', $stepsOrdered[1]->content);
    }
}
