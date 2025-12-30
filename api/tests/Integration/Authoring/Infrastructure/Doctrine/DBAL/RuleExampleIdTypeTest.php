<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\Authoring\Infrastructure\Doctrine\DBAL;

use Dairectiv\Authoring\Domain\Object\Rule\Example\ExampleId;
use Dairectiv\Tests\Framework\IntegrationTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

#[Group('integration')]
#[Group('authoring')]
#[Group('doctrine-type')]
final class RuleExampleIdTypeTest extends IntegrationTestCase
{
    private const string TYPE = 'authoring_rule_example_id';

    /**
     * @return iterable<string, array{phpValue: ?ExampleId, databaseValue: ?string}>
     */
    public static function provideValidValues(): iterable
    {
        yield 'nullable value' => [
            'phpValue'      => null,
            'databaseValue' => null,
        ];
        yield 'example id value' => [
            'phpValue'      => ExampleId::generate(true),
            'databaseValue' => null,
        ];
    }

    #[DataProvider('provideValidValues')]
    public function testItShouldConvertValueInBothWays(?ExampleId $phpValue, ?string $databaseValue): void
    {
        $databaseValue = $databaseValue ?? $phpValue?->toRfc4122();

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
