<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Unit\Authoring\Domain\Object\Skill;

use Dairectiv\Authoring\Domain\Object\Skill\SkillExample;
use Dairectiv\Authoring\Domain\Object\Skill\SkillExamples;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
#[Group('authoring')]
final class SkillExamplesTest extends TestCase
{
    public function testItShouldCreateEmptyExamples(): void
    {
        $examples = SkillExamples::empty();

        self::assertTrue($examples->isEmpty());
        self::assertCount(0, $examples);
    }

    public function testItShouldCreateFromList(): void
    {
        $example1 = SkillExample::create('Scenario 1', 'Input 1', 'Output 1');
        $example2 = SkillExample::create('Scenario 2', 'Input 2', 'Output 2');

        $examples = SkillExamples::fromList([$example1, $example2]);

        self::assertFalse($examples->isEmpty());
        self::assertCount(2, $examples);
    }

    public function testItShouldAddExample(): void
    {
        $examples = SkillExamples::empty();
        $example = SkillExample::create('Scenario', 'Input', 'Output');

        $newExamples = $examples->add($example);

        self::assertTrue($examples->isEmpty());
        self::assertCount(1, $newExamples);
    }

    public function testItShouldBeIterable(): void
    {
        $example1 = SkillExample::create('Scenario 1', 'Input 1', 'Output 1');
        $example2 = SkillExample::create('Scenario 2', 'Input 2', 'Output 2');
        $examples = SkillExamples::fromList([$example1, $example2]);

        $count = 0;
        foreach ($examples as $example) {
            self::assertNotEmpty($example->scenario);
            ++$count;
        }

        self::assertSame(2, $count);
    }

    public function testItShouldBeCountable(): void
    {
        $examples = SkillExamples::fromList([
            SkillExample::create('Scenario 1', 'Input 1', 'Output 1'),
            SkillExample::create('Scenario 2', 'Input 2', 'Output 2'),
            SkillExample::create('Scenario 3', 'Input 3', 'Output 3'),
        ]);

        self::assertCount(3, $examples);
    }

    public function testItShouldMapExamples(): void
    {
        $examples = SkillExamples::fromList([
            SkillExample::create('Scenario 1', 'Input 1', 'Output 1'),
            SkillExample::create('Scenario 2', 'Input 2', 'Output 2'),
        ]);

        $scenarios = $examples->map(static fn (SkillExample $e): string => $e->scenario);

        self::assertSame(['Scenario 1', 'Scenario 2'], $scenarios);
    }

    public function testItShouldConvertToArray(): void
    {
        $examples = SkillExamples::fromList([
            SkillExample::create('Scenario', 'Input', 'Output'),
        ]);

        $array = $examples->toArray();

        self::assertArrayHasKey('examples', $array);
        self::assertIsArray($array['examples']);
        self::assertCount(1, $array['examples']);
        self::assertIsArray($array['examples'][0]);
        self::assertSame('Scenario', $array['examples'][0]['scenario']);
    }

    public function testItShouldCreateFromArray(): void
    {
        $examples = SkillExamples::fromArray([
            'examples' => [
                ['scenario' => 'Scenario 1', 'input' => 'Input 1', 'output' => 'Output 1'],
                ['scenario' => 'Scenario 2', 'input' => 'Input 2', 'output' => 'Output 2'],
            ],
        ]);

        self::assertCount(2, $examples);
    }
}
