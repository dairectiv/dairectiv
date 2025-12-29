<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\Authoring\Infrastructure\Doctrine\DBAL;

use Dairectiv\Authoring\Domain\Object\Directive\Version\VersionId;
use Dairectiv\Tests\Framework\IntegrationTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

#[Group('integration')]
#[Group('authoring')]
#[Group('doctrine-type')]
final class VersionIdTypeTest extends IntegrationTestCase
{
    private const string TYPE = 'authoring_version_id';

    /**
     * @return iterable<string, array{phpValue: ?VersionId, databaseValue: ?string}>
     */
    public static function provideValidValues(): iterable
    {
        yield 'nullable value' => [
            'phpValue'      => null,
            'databaseValue' => null,
        ];
        yield 'version id value' => [
            'phpValue'      => VersionId::fromString('my-rule-v1'),
            'databaseValue' => 'my-rule-v1',
        ];
    }

    #[DataProvider('provideValidValues')]
    public function testItShouldConvertValueInBothWays(?VersionId $phpValue, ?string $databaseValue): void
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
