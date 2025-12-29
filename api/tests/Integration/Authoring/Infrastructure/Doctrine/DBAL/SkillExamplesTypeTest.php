<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\Authoring\Infrastructure\Doctrine\DBAL;

use Dairectiv\Authoring\Domain\Object\Skill\SkillExample;
use Dairectiv\Authoring\Domain\Object\Skill\SkillExamples;
use Dairectiv\Tests\Framework\IntegrationTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

#[Group('integration')]
#[Group('authoring')]
#[Group('doctrine-type')]
final class SkillExamplesTypeTest extends IntegrationTestCase
{
    private const string TYPE = 'object_value';

    /**
     * @return iterable<string, array{phpValue: ?SkillExamples, databaseValue: ?string}>
     */
    public static function provideValidValues(): iterable
    {
        yield 'nullable value' => [
            'phpValue'      => null,
            'databaseValue' => null,
        ];
        yield 'empty examples' => [
            'phpValue'      => SkillExamples::empty(),
            'databaseValue' => json_encode([
                'class' => SkillExamples::class,
                'state' => ['examples' => []],
            ], \JSON_THROW_ON_ERROR),
        ];
        yield 'example without explanation' => [
            'phpValue'      => SkillExamples::fromList([
                SkillExample::create('User asks to commit', 'git status output', 'feat: add feature'),
            ]),
            'databaseValue' => json_encode([
                'class' => SkillExamples::class,
                'state' => [
                    'examples' => [
                        [
                            'scenario'    => 'User asks to commit',
                            'input'       => 'git status output',
                            'output'      => 'feat: add feature',
                            'explanation' => null,
                        ],
                    ],
                ],
            ], \JSON_THROW_ON_ERROR),
        ];
        yield 'example with explanation' => [
            'phpValue'      => SkillExamples::fromList([
                SkillExample::create('Complex scenario', 'input data', 'output data', 'Detailed explanation'),
            ]),
            'databaseValue' => json_encode([
                'class' => SkillExamples::class,
                'state' => [
                    'examples' => [
                        [
                            'scenario'    => 'Complex scenario',
                            'input'       => 'input data',
                            'output'      => 'output data',
                            'explanation' => 'Detailed explanation',
                        ],
                    ],
                ],
            ], \JSON_THROW_ON_ERROR),
        ];
        yield 'multiple examples' => [
            'phpValue'      => SkillExamples::fromList([
                SkillExample::create('First scenario', 'input 1', 'output 1'),
                SkillExample::create('Second scenario', 'input 2', 'output 2', 'Explanation'),
            ]),
            'databaseValue' => json_encode([
                'class' => SkillExamples::class,
                'state' => [
                    'examples' => [
                        [
                            'scenario'    => 'First scenario',
                            'input'       => 'input 1',
                            'output'      => 'output 1',
                            'explanation' => null,
                        ],
                        [
                            'scenario'    => 'Second scenario',
                            'input'       => 'input 2',
                            'output'      => 'output 2',
                            'explanation' => 'Explanation',
                        ],
                    ],
                ],
            ], \JSON_THROW_ON_ERROR),
        ];
    }

    #[DataProvider('provideValidValues')]
    public function testItShouldConvertValueInBothWays(?SkillExamples $phpValue, ?string $databaseValue): void
    {
        self::assertConvertToDatabaseValue(
            $databaseValue,
            $phpValue,
            self::TYPE,
        );
        self::assertConvertToPhpValue(
            $phpValue,
            $databaseValue,
            self::TYPE,
        );
    }
}
