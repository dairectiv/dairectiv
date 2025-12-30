<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\Authoring\Application\Skill;

use Cake\Chronos\Chronos;
use Dairectiv\Authoring\Application\Skill\AddStep\Input;
use Dairectiv\Authoring\Application\Skill\AddStep\Output;
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
final class AddStepTest extends IntegrationTestCase
{
    public function testItShouldAddFirstStepToSkill(): void
    {
        $skill = self::draftSkill();
        $this->persistEntity($skill);

        self::assertCount(0, $skill->steps);

        $output = $this->execute(new Input((string) $skill->id, 'Step 1 content'));

        self::assertInstanceOf(Output::class, $output);
        self::assertSame('Step 1 content', $output->step->content);
        self::assertSame(1, $output->step->order);

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $persistedSkill = $this->findEntity(Skill::class, ['id' => $skill->id], true);

        self::assertCount(1, $persistedSkill->steps);
        $persistedStep = $persistedSkill->steps->first();
        self::assertInstanceOf(Step::class, $persistedStep);
        self::assertSame('Step 1 content', $persistedStep->content);
        self::assertSame(1, $persistedStep->order);
    }

    public function testItShouldAddMultipleStepsInOrder(): void
    {
        $skill = self::draftSkill();
        $this->persistEntity($skill);

        $output1 = $this->execute(new Input((string) $skill->id, 'Step 1'));
        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $output2 = $this->execute(new Input((string) $skill->id, 'Step 2'));
        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $output3 = $this->execute(new Input((string) $skill->id, 'Step 3'));
        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        self::assertInstanceOf(Output::class, $output1);
        self::assertSame(1, $output1->step->order);

        self::assertInstanceOf(Output::class, $output2);
        self::assertSame(1, $output2->step->order);

        self::assertInstanceOf(Output::class, $output3);
        self::assertSame(1, $output3->step->order);

        $persistedSkill = $this->findEntity(Skill::class, ['id' => $skill->id], true);

        self::assertCount(3, $persistedSkill->steps);

        $stepsOrdered = $persistedSkill->steps->toArray();
        self::assertSame('Step 3', $stepsOrdered[0]->content);
        self::assertSame(1, $stepsOrdered[0]->order);
        self::assertSame('Step 2', $stepsOrdered[1]->content);
        self::assertSame(2, $stepsOrdered[1]->order);
        self::assertSame('Step 1', $stepsOrdered[2]->content);
        self::assertSame(3, $stepsOrdered[2]->order);
    }

    public function testItShouldInsertStepAfterSpecificStep(): void
    {
        $skill = self::draftSkill();
        $step1 = Step::create($skill, 'Step 1');
        Step::create($skill, 'Step 2', $step1);
        $this->persistEntity($skill);

        self::assertCount(2, $skill->steps);

        $this->execute(new Input(
            (string) $skill->id,
            'Inserted Step',
            (string) $step1->id,
        ));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $persistedSkill = $this->findEntity(Skill::class, ['id' => $skill->id], true);

        self::assertCount(3, $persistedSkill->steps);

        $stepsOrdered = $persistedSkill->steps->toArray();
        self::assertSame('Step 1', $stepsOrdered[0]->content);
        self::assertSame(1, $stepsOrdered[0]->order);
        self::assertSame('Inserted Step', $stepsOrdered[1]->content);
        self::assertSame(2, $stepsOrdered[1]->order);
        self::assertSame('Step 2', $stepsOrdered[2]->content);
        self::assertSame(3, $stepsOrdered[2]->order);
    }

    public function testItShouldInsertStepAtEnd(): void
    {
        $skill = self::draftSkill();
        $step1 = Step::create($skill, 'Step 1');
        $step2 = Step::create($skill, 'Step 2', $step1);
        $this->persistEntity($skill);

        $this->execute(new Input(
            (string) $skill->id,
            'Last Step',
            (string) $step2->id,
        ));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $persistedSkill = $this->findEntity(Skill::class, ['id' => $skill->id], true);

        self::assertCount(3, $persistedSkill->steps);

        $stepsOrdered = $persistedSkill->steps->toArray();
        self::assertSame('Step 1', $stepsOrdered[0]->content);
        self::assertSame(1, $stepsOrdered[0]->order);
        self::assertSame('Step 2', $stepsOrdered[1]->content);
        self::assertSame(2, $stepsOrdered[1]->order);
        self::assertSame('Last Step', $stepsOrdered[2]->content);
        self::assertSame(3, $stepsOrdered[2]->order);
    }

    public function testItShouldThrowExceptionWhenSkillNotFound(): void
    {
        $this->expectException(DirectiveNotFoundException::class);

        $this->execute(new Input('non-existent-skill', 'Step content'));
    }

    public function testItShouldThrowExceptionWhenAfterStepNotFound(): void
    {
        $skill = self::draftSkill();
        $this->persistEntity($skill);

        $nonExistentId = '00000000-0000-0000-0000-000000000000';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(\sprintf('Step with ID "%s" not found.', $nonExistentId));

        $this->execute(new Input((string) $skill->id, 'Step content', $nonExistentId));
    }

    public function testItShouldThrowExceptionWhenSkillIsArchived(): void
    {
        $skill = self::draftSkill();
        $skill->archive();
        $this->persistEntity($skill);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot perform this action on an archived directive.');

        $this->execute(new Input((string) $skill->id, 'Step content'));
    }

    public function testItShouldPersistStepWithCorrectTimestamps(): void
    {
        $skill = self::draftSkill();
        $this->persistEntity($skill);

        Chronos::setTestNow(Chronos::now()->addDays(1));

        $output = $this->execute(new Input((string) $skill->id, 'Step content'));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        self::assertInstanceOf(Output::class, $output);
        self::assertTrue(Chronos::now()->equals($output->step->createdAt));
        self::assertTrue(Chronos::now()->equals($output->step->updatedAt));

        $persistedSkill = $this->findEntity(Skill::class, ['id' => $skill->id], true);
        $persistedStep = $persistedSkill->steps->first();

        self::assertInstanceOf(Step::class, $persistedStep);
        self::assertTrue($persistedSkill->createdAt->lessThan($persistedStep->createdAt));
        self::assertTrue($persistedSkill->createdAt->lessThan($persistedStep->updatedAt));
    }

    public function testItShouldGenerateUniqueStepId(): void
    {
        $skill = self::draftSkill();
        $this->persistEntity($skill);

        $output1 = $this->execute(new Input((string) $skill->id, 'Step 1'));
        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);
        self::assertInstanceOf(Output::class, $output1);

        $output2 = $this->execute(new Input((string) $skill->id, 'Step 2'));
        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);
        self::assertInstanceOf(Output::class, $output2);

        self::assertFalse($output1->step->id->equals($output2->step->id));
    }

    public function testItShouldLinkStepToCorrectSkill(): void
    {
        $skill = self::draftSkill();
        $this->persistEntity($skill);

        $output = $this->execute(new Input((string) $skill->id, 'Step content'));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        self::assertInstanceOf(Output::class, $output);
        self::assertTrue($output->step->skill->id->equals($skill->id));
    }
}
