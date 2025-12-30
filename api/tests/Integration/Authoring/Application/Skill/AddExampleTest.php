<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\Authoring\Application\Skill;

use Dairectiv\Authoring\Application\Skill\AddExample\Input;
use Dairectiv\Authoring\Application\Skill\AddExample\Output;
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
final class AddExampleTest extends IntegrationTestCase
{
    public function testItShouldAddExampleToSkill(): void
    {
        $skill = self::draftSkill();
        $this->persistEntity($skill);

        $output = $this->executeAddExample(
            (string) $skill->id,
            'Test scenario',
            'Test input',
            'Test output',
            'Test explanation',
        );

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $example = $output->example;

        self::assertSame('Test scenario', $example->scenario);
        self::assertSame('Test input', $example->input);
        self::assertSame('Test output', $example->output);
        self::assertSame('Test explanation', $example->explanation);

        // Verify persistence
        $persistedSkill = $this->findEntity(Skill::class, ['id' => $skill->id], true);

        self::assertCount(1, $persistedSkill->examples);

        $persistedExample = $persistedSkill->examples->first();

        self::assertSame('Test scenario', $persistedExample->scenario);
        self::assertSame('Test input', $persistedExample->input);
        self::assertSame('Test output', $persistedExample->output);
        self::assertSame('Test explanation', $persistedExample->explanation);
    }

    public function testItShouldAddExampleWithoutExplanation(): void
    {
        $skill = self::draftSkill();
        $this->persistEntity($skill);

        $output = $this->executeAddExample(
            (string) $skill->id,
            'Test scenario',
            'Test input',
            'Test output',
        );

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        self::assertNull($output->example->explanation);

        $persistedSkill = $this->findEntity(Skill::class, ['id' => $skill->id], true);
        $persistedExample = $persistedSkill->examples->first();

        self::assertNull($persistedExample->explanation);
    }

    public function testItShouldAddMultipleExamplesToSkill(): void
    {
        $skill = self::draftSkill();
        $this->persistEntity($skill);

        $this->executeAddExample((string) $skill->id, 'Scenario 1', 'Input 1', 'Output 1');
        $this->executeAddExample((string) $skill->id, 'Scenario 2', 'Input 2', 'Output 2');
        $this->executeAddExample((string) $skill->id, 'Scenario 3', 'Input 3', 'Output 3');

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class, 3);

        $persistedSkill = $this->findEntity(Skill::class, ['id' => $skill->id], true);

        self::assertCount(3, $persistedSkill->examples);
    }

    public function testItShouldThrowExceptionWhenSkillNotFound(): void
    {
        $this->expectException(DirectiveNotFoundException::class);

        $this->executeAddExample('non-existent-skill', 'Scenario', 'Input', 'Output');
    }

    public function testItShouldThrowExceptionWhenSkillIsArchived(): void
    {
        $skill = self::draftSkill();
        $skill->archive();
        $this->persistEntity($skill);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot perform this action on an archived directive.');

        $this->executeAddExample((string) $skill->id, 'Scenario', 'Input', 'Output');
    }

    public function testItShouldAddExampleToPublishedSkill(): void
    {
        $skill = self::draftSkill();
        $skill->publish();
        $this->persistEntity($skill);

        $output = $this->executeAddExample(
            (string) $skill->id,
            'Test scenario',
            'Test input',
            'Test output',
        );

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);
        self::assertSame('Test scenario', $output->example->scenario);
    }

    private function executeAddExample(
        string $skillId,
        string $scenario,
        string $input,
        string $output,
        ?string $explanation = null,
    ): Output {
        $result = $this->execute(new Input($skillId, $scenario, $input, $output, $explanation));

        self::assertInstanceOf(Output::class, $result);

        return $result;
    }
}
