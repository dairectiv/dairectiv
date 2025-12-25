<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\Authoring\Infrastructure\Doctrine\DBAL;

use Dairectiv\Authoring\Domain\Object\Directive\Version\VersionNumber;
use Dairectiv\Tests\Framework\IntegrationTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class VersionNumberTypeTest extends IntegrationTestCase
{
    private const string TYPE = 'authoring_version_number';

    /**
     * @return iterable<string, array{phpValue: ?VersionNumber, databaseValue: ?int}>
     */
    public static function provideValidValues(): iterable
    {
        yield 'nullable value' => [
            'phpValue'      => null,
            'databaseValue' => null,
        ];
        yield 'initial version number' => [
            'phpValue'      => VersionNumber::initial(),
            'databaseValue' => 1,
        ];
        yield 'incremented version number' => [
            'phpValue'      => VersionNumber::fromInt(5),
            'databaseValue' => 5,
        ];
    }

    #[DataProvider('provideValidValues')]
    public function testItShouldConvertValueInBothWays(?VersionNumber $phpValue, ?int $databaseValue): void
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
