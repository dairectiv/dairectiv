<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\Authoring\Infrastructure\Doctrine\DBAL;

use Dairectiv\Authoring\Domain\Object\Directive\DirectiveId;
use Dairectiv\Tests\Framework\IntegrationTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

#[Group('integration')]
#[Group('authoring')]
#[Group('doctrine-type')]
final class DirectiveIdTypeTest extends IntegrationTestCase
{
    private const string TYPE = 'authoring_directive_id';

    /**
     * @return iterable<string, array{phpValue: ?DirectiveId, databaseValue: ?string}>
     */
    public static function provideValidValues(): iterable
    {
        yield 'nullable value' => [
            'phpValue'      => null,
            'databaseValue' => null,
        ];
        yield 'directive id value' => [
            'phpValue'      => DirectiveId::fromString('my-rule'),
            'databaseValue' => 'my-rule',
        ];
    }

    #[DataProvider('provideValidValues')]
    public function testItShouldConvertValueInBothWays(?DirectiveId $phpValue, ?string $databaseValue): void
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
