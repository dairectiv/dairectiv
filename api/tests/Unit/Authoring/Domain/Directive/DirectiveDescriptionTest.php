<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Unit\Authoring\Domain\Directive;

use Dairectiv\Authoring\Domain\Directive\DirectiveDescription;
use Dairectiv\SharedKernel\Domain\Exception\InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class DirectiveDescriptionTest extends TestCase
{
    public function testItShouldCreateDirectiveDescriptionFromValidString(): void
    {
        $description = DirectiveDescription::fromString('A short description of the directive.');

        self::assertSame('A short description of the directive.', $description->description);
    }

    public function testItShouldReturnDescriptionAsStringWhenCastToString(): void
    {
        $description = DirectiveDescription::fromString('My directive description');

        self::assertSame('My directive description', (string) $description);
    }

    #[DataProvider('validDescriptionsProvider')]
    public function testItShouldAcceptValidDescriptions(string $value): void
    {
        $description = DirectiveDescription::fromString($value);

        self::assertSame($value, $description->description);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function validDescriptionsProvider(): iterable
    {
        yield 'simple description' => ['A simple directive description'];
        yield 'single character' => ['A'];
        yield 'with numbers' => ['Directive v2.0 description'];
        yield 'with special chars' => ['Directive: use sprintf() for formatting!'];
        yield 'max length (255 chars)' => [str_repeat('a', 255)];
    }

    public function testItShouldThrowExceptionWhenDescriptionIsEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Directive description cannot be empty.');

        DirectiveDescription::fromString('');
    }

    public function testItShouldThrowExceptionWhenDescriptionIsTooLong(): void
    {
        $longDescription = str_repeat('a', 256);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Directive description is too long.');

        DirectiveDescription::fromString($longDescription);
    }

    public function testItShouldAcceptDescriptionWithExactlyMaxLength(): void
    {
        $maxLengthDescription = str_repeat('x', 255);

        $description = DirectiveDescription::fromString($maxLengthDescription);

        self::assertSame(255, \strlen($description->description));
    }

    public function testItShouldBeImmutable(): void
    {
        $description = DirectiveDescription::fromString('original description');

        self::assertSame('original description', $description->description);
    }
}
