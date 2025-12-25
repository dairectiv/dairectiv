<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Unit\Authoring\Domain\Object\Directive\Metadata;

use Dairectiv\Authoring\Domain\Object\Directive\Metadata\DirectiveName;
use Dairectiv\SharedKernel\Domain\Object\Exception\InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class DirectiveNameTest extends TestCase
{
    public function testItShouldCreateDirectiveNameFromValidString(): void
    {
        $name = DirectiveName::fromString('my-directive-name');

        self::assertSame('my-directive-name', $name->name);
    }

    public function testItShouldReturnNameAsStringWhenCastToString(): void
    {
        $name = DirectiveName::fromString('my-directive');

        self::assertSame('my-directive', (string) $name);
    }

    #[DataProvider('validNamesProvider')]
    public function testItShouldAcceptValidNames(string $value): void
    {
        $name = DirectiveName::fromString($value);

        self::assertSame($value, $name->name);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function validNamesProvider(): iterable
    {
        yield 'simple name' => ['my-name'];
        yield 'single character' => ['a'];
        yield 'with spaces' => ['My Directive Name'];
        yield 'with numbers' => ['directive-123'];
        yield 'with special chars' => ['directive_name.v2'];
        yield 'max length (255 chars)' => [str_repeat('a', 255)];
    }

    public function testItShouldThrowExceptionWhenNameIsEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Directive name cannot be empty.');

        DirectiveName::fromString('');
    }

    public function testItShouldThrowExceptionWhenNameIsTooLong(): void
    {
        $longName = str_repeat('a', 256);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Directive name is too long.');

        DirectiveName::fromString($longName);
    }

    public function testItShouldAcceptNameWithExactlyMaxLength(): void
    {
        $maxLengthName = str_repeat('x', 255);

        $name = DirectiveName::fromString($maxLengthName);

        self::assertSame(255, \strlen($name->name));
    }

    public function testItShouldBeImmutable(): void
    {
        $name = DirectiveName::fromString('original-name');

        self::assertSame('original-name', $name->name);
    }
}
