<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Unit\Authoring\Domain\Object\Directive;

use Dairectiv\Authoring\Domain\Object\Directive\DirectiveId;
use Dairectiv\SharedKernel\Domain\Object\Exception\InvalidArgumentException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
#[Group('authoring')]
final class DirectiveIdTest extends TestCase
{
    public function testItShouldCreateDirectiveIdFromValidKebabCaseString(): void
    {
        $id = DirectiveId::fromString('my-directive');

        self::assertSame('my-directive', $id->id);
    }

    public function testItShouldThrowExceptionWhenStringIsNotKebabCase(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Directive ID "MyDirective" is not in kebab-case.');

        DirectiveId::fromString('MyDirective');
    }

    public function testItShouldThrowExceptionForCamelCaseString(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Directive ID "myDirective" is not in kebab-case.');

        DirectiveId::fromString('myDirective');
    }

    public function testItShouldThrowExceptionForSnakeCaseString(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Directive ID "my_directive" is not in kebab-case.');

        DirectiveId::fromString('my_directive');
    }

    public function testItShouldThrowExceptionForStringWithSpaces(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Directive ID "my directive" is not in kebab-case.');

        DirectiveId::fromString('my directive');
    }

    public function testItShouldReturnStringRepresentation(): void
    {
        $id = DirectiveId::fromString('my-directive');

        self::assertSame('my-directive', (string) $id);
        self::assertSame('my-directive', $id->__toString());
    }

    public function testItShouldHandleSingleWordKebabCase(): void
    {
        $id = DirectiveId::fromString('directive');

        self::assertSame('directive', $id->id);
        self::assertSame('directive', (string) $id);
    }

    public function testItShouldHandleMultipleHyphens(): void
    {
        $id = DirectiveId::fromString('my-very-long-directive-name');

        self::assertSame('my-very-long-directive-name', $id->id);
        self::assertSame('my-very-long-directive-name', (string) $id);
    }

    public function testItShouldThrowExceptionForUpperCaseLetters(): void
    {
        $this->expectException(InvalidArgumentException::class);

        DirectiveId::fromString('MY-DIRECTIVE');
    }

    public function testItShouldThrowExceptionForEmptyString(): void
    {
        $this->expectException(InvalidArgumentException::class);

        DirectiveId::fromString('');
    }
}
