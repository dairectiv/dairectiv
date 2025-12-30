<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Unit\SharedKernel\Domain\Object;

use Dairectiv\SharedKernel\Domain\Object\StringNormalizer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
#[Group('shared-kernel')]
final class StringNormalizerTest extends TestCase
{
    use StringNormalizer;

    /**
     * @return iterable<string, array{input: string, expected: string}>
     */
    public static function provideTrimCases(): iterable
    {
        yield 'no whitespace' => ['input' => 'hello', 'expected' => 'hello'];
        yield 'leading spaces' => ['input' => '  hello', 'expected' => 'hello'];
        yield 'trailing spaces' => ['input' => 'hello  ', 'expected' => 'hello'];
        yield 'both leading and trailing' => ['input' => '  hello  ', 'expected' => 'hello'];
        yield 'internal spaces preserved' => ['input' => '  hello  world  ', 'expected' => 'hello  world'];
        yield 'tabs' => ['input' => "\thello\t", 'expected' => 'hello'];
        yield 'newlines' => ['input' => "\nhello\n", 'expected' => 'hello'];
        yield 'mixed whitespace' => ['input' => " \t\n hello \n\t ", 'expected' => 'hello'];
        yield 'only spaces' => ['input' => '   ', 'expected' => ''];
        yield 'empty string' => ['input' => '', 'expected' => ''];
    }

    #[DataProvider('provideTrimCases')]
    public function testItShouldTrimString(string $input, string $expected): void
    {
        self::assertSame($expected, self::trim($input));
    }

    /**
     * @return iterable<string, array{input: ?string, expected: ?string}>
     */
    public static function provideTrimOrNullCases(): iterable
    {
        yield 'null returns null' => ['input' => null, 'expected' => null];
        yield 'no whitespace' => ['input' => 'hello', 'expected' => 'hello'];
        yield 'leading spaces' => ['input' => '  hello', 'expected' => 'hello'];
        yield 'trailing spaces' => ['input' => 'hello  ', 'expected' => 'hello'];
        yield 'both leading and trailing' => ['input' => '  hello  ', 'expected' => 'hello'];
        yield 'internal spaces preserved' => ['input' => '  hello  world  ', 'expected' => 'hello  world'];
        yield 'only spaces' => ['input' => '   ', 'expected' => ''];
        yield 'empty string' => ['input' => '', 'expected' => ''];
    }

    #[DataProvider('provideTrimOrNullCases')]
    public function testItShouldTrimOrNullString(?string $input, ?string $expected): void
    {
        self::assertSame($expected, self::trimOrNull($input));
    }
}
