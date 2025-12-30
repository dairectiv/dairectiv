<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\SharedKernel\Infrastructure\Doctrine\DBAL;

use Dairectiv\Tests\Fixtures\Domain\FakeObjectValue;
use Dairectiv\Tests\Framework\IntegrationTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

#[Group('integration')]
#[Group('doctrine-type')]
#[Group('shared-kernel')]
final class ObjectValueTypeTest extends IntegrationTestCase
{
    private const string TYPE = 'object_value';

    /**
     * @return iterable<string, array{phpValue: ?FakeObjectValue, databaseValue: ?string}>
     */
    public static function provideValidValues(): iterable
    {
        yield 'null' => [
            'phpValue'      => null,
            'databaseValue' => null,
        ];

        yield 'simple object with scalar values' => [
            'phpValue'      => new FakeObjectValue('test', 42),
            'databaseValue' => \sprintf(
                '{"class":"%s","state":{"name":"test","count":42,"optional":null,"metadata":[]}}',
                str_replace('\\', '\\\\', FakeObjectValue::class),
            ),
        ];

        yield 'object with optional value' => [
            'phpValue'      => new FakeObjectValue('example', 10, 'optional-value'),
            'databaseValue' => \sprintf(
                '{"class":"%s","state":{"name":"example","count":10,"optional":"optional-value","metadata":[]}}',
                str_replace('\\', '\\\\', FakeObjectValue::class),
            ),
        ];

        yield 'object with nested metadata' => [
            'phpValue'      => new FakeObjectValue('complex', 99, null, ['key' => 'value', 'nested' => ['foo' => 'bar']]),
            'databaseValue' => \sprintf(
                '{"class":"%s","state":{"name":"complex","count":99,"optional":null,"metadata":{"key":"value","nested":{"foo":"bar"}}}}',
                str_replace('\\', '\\\\', FakeObjectValue::class),
            ),
        ];
    }

    #[DataProvider('provideValidValues')]
    public function testItShouldConvertToDatabaseValue(?FakeObjectValue $phpValue, ?string $databaseValue): void
    {
        self::assertConvertToDatabaseValue(
            $databaseValue,
            $phpValue,
            self::TYPE,
        );
    }

    #[DataProvider('provideValidValues')]
    public function testItShouldConvertToPhpValue(?FakeObjectValue $phpValue, ?string $databaseValue): void
    {
        $convertedValue = $this->getEntityManager()->getConnection()->convertToPHPValue($databaseValue, self::TYPE);

        if (null === $phpValue) {
            self::assertNull($convertedValue);
        } else {
            self::assertInstanceOf(FakeObjectValue::class, $convertedValue);
            self::assertSame($phpValue->name, $convertedValue->name);
            self::assertSame($phpValue->count, $convertedValue->count);
            self::assertSame($phpValue->optional, $convertedValue->optional);
            self::assertEquals($phpValue->metadata, $convertedValue->metadata);
        }
    }
}
