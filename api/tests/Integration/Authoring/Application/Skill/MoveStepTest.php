<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\Authoring\Application\Skill;

use Dairectiv\Authoring\Application\Skill\MoveStep\Input;
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
final class MoveStepTest extends IntegrationTestCase
{
    public function testItShouldMoveStepToBeginning(): void
    {
        $skill = self::draftSkill();
        $step1 = Step::create($skill, 'Step 1');
        $step2 = Step::create($skill, 'Step 2', $step1);
        $step3 = Step::create($skill, 'Step 3', $step2);
        $this->persistEntity($skill);

        $this->execute(new Input((string) $skill->id, (string) $step3->id, null));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $persistedSkill = $this->findEntity(Skill::class, ['id' => $skill->id], true);

        $stepsOrdered = $persistedSkill->steps->toArray();
        self::assertSame('Step 3', $stepsOrdered[0]->content);
        self::assertSame(1, $stepsOrdered[0]->order);
        self::assertSame('Step 1', $stepsOrdered[1]->content);
        self::assertSame(2, $stepsOrdered[1]->order);
        self::assertSame('Step 2', $stepsOrdered[2]->content);
        self::assertSame(3, $stepsOrdered[2]->order);
    }

    public function testItShouldMoveStepAfterAnotherStep(): void
    {
        $skill = self::draftSkill();
        $step1 = Step::create($skill, 'Step 1');
        $step2 = Step::create($skill, 'Step 2', $step1);
        $step3 = Step::create($skill, 'Step 3', $step2);
        $this->persistEntity($skill);

        $this->execute(new Input((string) $skill->id, (string) $step3->id, (string) $step1->id));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $persistedSkill = $this->findEntity(Skill::class, ['id' => $skill->id], true);

        $stepsOrdered = $persistedSkill->steps->toArray();
        self::assertSame('Step 1', $stepsOrdered[0]->content);
        self::assertSame(1, $stepsOrdered[0]->order);
        self::assertSame('Step 3', $stepsOrdered[1]->content);
        self::assertSame(2, $stepsOrdered[1]->order);
        self::assertSame('Step 2', $stepsOrdered[2]->content);
        self::assertSame(3, $stepsOrdered[2]->order);
    }

    public function testItShouldMoveFirstStepToEnd(): void
    {
        $skill = self::draftSkill();
        $step1 = Step::create($skill, 'Step 1');
        $step2 = Step::create($skill, 'Step 2', $step1);
        $step3 = Step::create($skill, 'Step 3', $step2);
        $this->persistEntity($skill);

        $this->execute(new Input((string) $skill->id, (string) $step1->id, (string) $step3->id));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $persistedSkill = $this->findEntity(Skill::class, ['id' => $skill->id], true);

        $stepsOrdered = $persistedSkill->steps->toArray();
        self::assertSame('Step 2', $stepsOrdered[0]->content);
        self::assertSame(1, $stepsOrdered[0]->order);
        self::assertSame('Step 3', $stepsOrdered[1]->content);
        self::assertSame(2, $stepsOrdered[1]->order);
        self::assertSame('Step 1', $stepsOrdered[2]->content);
        self::assertSame(3, $stepsOrdered[2]->order);
    }

    public function testItShouldDoNothingWhenMovingAfterItself(): void
    {
        $skill = self::draftSkill();
        $step1 = Step::create($skill, 'Step 1');
        $step2 = Step::create($skill, 'Step 2', $step1);
        $step3 = Step::create($skill, 'Step 3', $step2);
        $this->persistEntity($skill);

        $this->execute(new Input((string) $skill->id, (string) $step2->id, (string) $step2->id));

        // No domain event should be dispatched since nothing changed
        $persistedSkill = $this->findEntity(Skill::class, ['id' => $skill->id], true);

        $stepsOrdered = $persistedSkill->steps->toArray();
        self::assertSame('Step 1', $stepsOrdered[0]->content);
        self::assertSame(1, $stepsOrdered[0]->order);
        self::assertSame('Step 2', $stepsOrdered[1]->content);
        self::assertSame(2, $stepsOrdered[1]->order);
        self::assertSame('Step 3', $stepsOrdered[2]->content);
        self::assertSame(3, $stepsOrdered[2]->order);
    }

    public function testItShouldDoNothingWhenStepAlreadyInPosition(): void
    {
        $skill = self::draftSkill();
        $step1 = Step::create($skill, 'Step 1');
        $step2 = Step::create($skill, 'Step 2', $step1);
        $step3 = Step::create($skill, 'Step 3', $step2);
        $this->persistEntity($skill);

        $this->execute(new Input((string) $skill->id, (string) $step2->id, (string) $step1->id));

        // No domain event should be dispatched since nothing changed
        $persistedSkill = $this->findEntity(Skill::class, ['id' => $skill->id], true);

        $stepsOrdered = $persistedSkill->steps->toArray();
        self::assertSame('Step 1', $stepsOrdered[0]->content);
        self::assertSame(1, $stepsOrdered[0]->order);
        self::assertSame('Step 2', $stepsOrdered[1]->content);
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
            null,
        ));
    }

    public function testItShouldThrowExceptionWhenStepNotFound(): void
    {
        $skill = self::draftSkill();
        $this->persistEntity($skill);

        $nonExistentId = '00000000-0000-0000-0000-000000000000';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(\sprintf('Step with ID "%s" not found.', $nonExistentId));

        $this->execute(new Input((string) $skill->id, $nonExistentId, null));
    }

    public function testItShouldThrowExceptionWhenReferenceStepNotFound(): void
    {
        $skill = self::draftSkill();
        $step = Step::create($skill, 'Step');
        $this->persistEntity($skill);

        $nonExistentId = '00000000-0000-0000-0000-000000000000';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(\sprintf('Reference step with ID "%s" not found.', $nonExistentId));

        $this->execute(new Input((string) $skill->id, (string) $step->id, $nonExistentId));
    }

    public function testItShouldThrowExceptionWhenSkillIsArchived(): void
    {
        $skill = self::draftSkill();
        $step1 = Step::create($skill, 'Step 1');
        $step2 = Step::create($skill, 'Step 2', $step1);
        $skill->archive();
        $this->persistEntity($skill);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot perform this action on an archived directive.');

        $this->execute(new Input((string) $skill->id, (string) $step2->id, null));
    }

    public function testItShouldMoveMiddleStepForward(): void
    {
        $skill = self::draftSkill();
        $step1 = Step::create($skill, 'Step 1');
        $step2 = Step::create($skill, 'Step 2', $step1);
        $step3 = Step::create($skill, 'Step 3', $step2);
        $step4 = Step::create($skill, 'Step 4', $step3);
        $this->persistEntity($skill);

        $this->execute(new Input((string) $skill->id, (string) $step2->id, (string) $step3->id));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $persistedSkill = $this->findEntity(Skill::class, ['id' => $skill->id], true);

        $stepsOrdered = $persistedSkill->steps->toArray();
        self::assertSame('Step 1', $stepsOrdered[0]->content);
        self::assertSame(1, $stepsOrdered[0]->order);
        self::assertSame('Step 3', $stepsOrdered[1]->content);
        self::assertSame(2, $stepsOrdered[1]->order);
        self::assertSame('Step 2', $stepsOrdered[2]->content);
        self::assertSame(3, $stepsOrdered[2]->order);
        self::assertSame('Step 4', $stepsOrdered[3]->content);
        self::assertSame(4, $stepsOrdered[3]->order);
    }

    public function testItShouldMoveMiddleStepBackward(): void
    {
        $skill = self::draftSkill();
        $step1 = Step::create($skill, 'Step 1');
        $step2 = Step::create($skill, 'Step 2', $step1);
        $step3 = Step::create($skill, 'Step 3', $step2);
        $step4 = Step::create($skill, 'Step 4', $step3);
        $this->persistEntity($skill);

        $this->execute(new Input((string) $skill->id, (string) $step3->id, null));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $persistedSkill = $this->findEntity(Skill::class, ['id' => $skill->id], true);

        $stepsOrdered = $persistedSkill->steps->toArray();
        self::assertSame('Step 3', $stepsOrdered[0]->content);
        self::assertSame(1, $stepsOrdered[0]->order);
        self::assertSame('Step 1', $stepsOrdered[1]->content);
        self::assertSame(2, $stepsOrdered[1]->order);
        self::assertSame('Step 2', $stepsOrdered[2]->content);
        self::assertSame(3, $stepsOrdered[2]->order);
        self::assertSame('Step 4', $stepsOrdered[3]->content);
        self::assertSame(4, $stepsOrdered[3]->order);
    }
}
