<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\SharedKernel\Infrastructure\Doctrine\DBAL;

use Cake\Chronos\Chronos;
use Dairectiv\Tests\Framework\IntegrationTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class ChronosTypeTest extends IntegrationTestCase
{
    private const string TYPE = 'chronos';

    /**
     * @return iterable<string, array{phpValue: ?Chronos, databaseValue: ?string}>
     */
    public static function provideValidValues(): iterable
    {
        yield 'nullable value' => [
            'phpValue'      => null,
            'databaseValue' => null,
        ];
        yield 'datetime value' => [
            'phpValue'      => new Chronos('2024-01-15 10:30:00'),
            'databaseValue' => '2024-01-15 10:30:00',
        ];
    }

    #[DataProvider('provideValidValues')]
    public function testItShouldConvertValueInBothWays(?Chronos $phpValue, ?string $databaseValue): void
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
