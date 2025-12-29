<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\Authoring\Infrastructure\Doctrine\DBAL;

use Dairectiv\Authoring\Domain\Object\Rule\RuleExample;
use Dairectiv\Authoring\Domain\Object\Rule\RuleExamples;
use Dairectiv\Tests\Framework\IntegrationTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

#[Group('integration')]
#[Group('authoring')]
#[Group('doctrine-type')]
final class RuleExamplesTypeTest extends IntegrationTestCase
{
    private const string TYPE = 'object_value';

    /**
     * @return iterable<string, array{phpValue: ?RuleExamples, databaseValue: ?string}>
     */
    public static function provideValidValues(): iterable
    {
        yield 'nullable value' => [
            'phpValue'      => null,
            'databaseValue' => null,
        ];
        yield 'empty examples' => [
            'phpValue'      => RuleExamples::empty(),
            'databaseValue' => \Safe\json_encode([
                'class' => RuleExamples::class,
                'state' => ['examples' => []],
            ], \JSON_THROW_ON_ERROR),
        ];
        yield 'good example' => [
            'phpValue'      => RuleExamples::fromList([
                RuleExample::good('readonly class Foo {}', 'Immutable class'),
            ]),
            'databaseValue' => \Safe\json_encode([
                'class' => RuleExamples::class,
                'state' => [
                    'examples' => [
                        ['good' => 'readonly class Foo {}', 'bad' => null, 'explanation' => 'Immutable class'],
                    ],
                ],
            ], \JSON_THROW_ON_ERROR),
        ];
        yield 'bad example' => [
            'phpValue'      => RuleExamples::fromList([
                RuleExample::bad('class Foo {}', 'Mutable class'),
            ]),
            'databaseValue' => \Safe\json_encode([
                'class' => RuleExamples::class,
                'state' => [
                    'examples' => [
                        ['good' => null, 'bad' => 'class Foo {}', 'explanation' => 'Mutable class'],
                    ],
                ],
            ], \JSON_THROW_ON_ERROR),
        ];
        yield 'transformation example' => [
            'phpValue'      => RuleExamples::fromList([
                RuleExample::transformation('class Foo {}', 'readonly class Foo {}', 'Prefer immutability'),
            ]),
            'databaseValue' => \Safe\json_encode([
                'class' => RuleExamples::class,
                'state' => [
                    'examples' => [
                        ['good' => 'readonly class Foo {}', 'bad' => 'class Foo {}', 'explanation' => 'Prefer immutability'],
                    ],
                ],
            ], \JSON_THROW_ON_ERROR),
        ];
    }

    #[DataProvider('provideValidValues')]
    public function testItShouldConvertValueInBothWays(?RuleExamples $phpValue, ?string $databaseValue): void
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
