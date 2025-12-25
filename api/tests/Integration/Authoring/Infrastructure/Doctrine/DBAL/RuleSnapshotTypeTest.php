<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\Authoring\Infrastructure\Doctrine\DBAL;

use Dairectiv\Authoring\Domain\Rule\RuleSnapshot;
use Dairectiv\Tests\Framework\IntegrationTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class RuleSnapshotTypeTest extends IntegrationTestCase
{
    private const string TYPE = 'object_value';

    /**
     * @return iterable<string, array{phpValue: ?RuleSnapshot, databaseValue: ?string}>
     */
    public static function provideValidValues(): iterable
    {
        yield 'nullable value' => [
            'phpValue'      => null,
            'databaseValue' => null,
        ];
        yield 'snapshot with empty examples' => [
            'phpValue' => RuleSnapshot::fromArray([
                'content'  => 'Always use strict types',
                'examples' => ['examples' => []],
            ]),
            'databaseValue' => json_encode([
                'class' => RuleSnapshot::class,
                'state' => [
                    'content'  => 'Always use strict types',
                    'examples' => ['examples' => []],
                ],
            ], \JSON_THROW_ON_ERROR),
        ];
        yield 'snapshot with examples' => [
            'phpValue' => RuleSnapshot::fromArray([
                'content'  => 'Use readonly classes',
                'examples' => [
                    'examples' => [
                        ['good' => 'readonly class Foo {}', 'bad' => null, 'explanation' => 'Good practice'],
                    ],
                ],
            ]),
            'databaseValue' => json_encode([
                'class' => RuleSnapshot::class,
                'state' => [
                    'content'  => 'Use readonly classes',
                    'examples' => [
                        'examples' => [
                            ['good' => 'readonly class Foo {}', 'bad' => null, 'explanation' => 'Good practice'],
                        ],
                    ],
                ],
            ], \JSON_THROW_ON_ERROR),
        ];
    }

    #[DataProvider('provideValidValues')]
    public function testItShouldConvertValueInBothWays(?RuleSnapshot $phpValue, ?string $databaseValue): void
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
