<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Unit\Authoring\Domain\Object\Skill;

use Dairectiv\Authoring\Domain\Object\Skill\SkillExample;
use Dairectiv\SharedKernel\Domain\Object\Exception\InvalidArgumentException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
#[Group('authoring')]
final class SkillExampleTest extends TestCase
{
    public function testItShouldCreateSkillExample(): void
    {
        $example = SkillExample::create(
            'User asks to commit changes',
            'git status showing modified files',
            'feat: add new feature',
            'The commit message follows conventional commits',
        );

        self::assertSame('User asks to commit changes', $example->scenario);
        self::assertSame('git status showing modified files', $example->input);
        self::assertSame('feat: add new feature', $example->output);
        self::assertSame('The commit message follows conventional commits', $example->explanation);
        self::assertTrue($example->hasExplanation());
    }

    public function testItShouldCreateSkillExampleWithoutExplanation(): void
    {
        $example = SkillExample::create(
            'Scenario',
            'Input',
            'Output',
        );

        self::assertNull($example->explanation);
        self::assertFalse($example->hasExplanation());
    }

    public function testItShouldThrowExceptionWhenScenarioIsEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Skill example scenario cannot be empty.');

        SkillExample::create('', 'input', 'output');
    }

    public function testItShouldThrowExceptionWhenInputIsEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Skill example input cannot be empty.');

        SkillExample::create('scenario', '', 'output');
    }

    public function testItShouldThrowExceptionWhenOutputIsEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Skill example output cannot be empty.');

        SkillExample::create('scenario', 'input', '');
    }

    public function testItShouldConvertToArray(): void
    {
        $example = SkillExample::create('scenario', 'input', 'output', 'explanation');

        $array = $example->toArray();

        self::assertSame([
            'scenario'    => 'scenario',
            'input'       => 'input',
            'output'      => 'output',
            'explanation' => 'explanation',
        ], $array);
    }

    public function testItShouldCreateFromArray(): void
    {
        $example = SkillExample::fromArray([
            'scenario'    => 'scenario',
            'input'       => 'input',
            'output'      => 'output',
            'explanation' => 'explanation',
        ]);

        self::assertSame('scenario', $example->scenario);
        self::assertSame('input', $example->input);
        self::assertSame('output', $example->output);
        self::assertSame('explanation', $example->explanation);
    }

    public function testItShouldCreateFromArrayWithoutExplanation(): void
    {
        $example = SkillExample::fromArray([
            'scenario' => 'scenario',
            'input'    => 'input',
            'output'   => 'output',
        ]);

        self::assertNull($example->explanation);
    }
}
