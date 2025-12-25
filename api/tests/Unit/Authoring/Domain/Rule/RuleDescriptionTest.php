<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Unit\Authoring\Domain\Rule;

use Dairectiv\Authoring\Domain\Rule\RuleDescription;
use Dairectiv\SharedKernel\Domain\Exception\InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class RuleDescriptionTest extends TestCase
{
    public function testItShouldCreateRuleDescriptionFromValidString(): void
    {
        $description = RuleDescription::fromString('A short description of the rule.');

        self::assertSame('A short description of the rule.', $description->description);
    }

    public function testItShouldReturnDescriptionAsStringWhenCastToString(): void
    {
        $description = RuleDescription::fromString('My rule description');

        self::assertSame('My rule description', (string) $description);
    }

    #[DataProvider('validDescriptionsProvider')]
    public function testItShouldAcceptValidDescriptions(string $value): void
    {
        $description = RuleDescription::fromString($value);

        self::assertSame($value, $description->description);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function validDescriptionsProvider(): iterable
    {
        yield 'simple description' => ['A simple rule description'];
        yield 'single character' => ['A'];
        yield 'with numbers' => ['Rule v2.0 description'];
        yield 'with special chars' => ['Rule: use sprintf() for formatting!'];
        yield 'max length (255 chars)' => [str_repeat('a', 255)];
    }

    public function testItShouldThrowExceptionWhenDescriptionIsEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Rule description cannot be empty.');

        RuleDescription::fromString('');
    }

    public function testItShouldThrowExceptionWhenDescriptionIsTooLong(): void
    {
        $longDescription = str_repeat('a', 256);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Rule description is too long.');

        RuleDescription::fromString($longDescription);
    }

    public function testItShouldAcceptDescriptionWithExactlyMaxLength(): void
    {
        $maxLengthDescription = str_repeat('x', 255);

        $description = RuleDescription::fromString($maxLengthDescription);

        self::assertSame(255, \strlen($description->description));
    }

    public function testItShouldBeImmutable(): void
    {
        $description = RuleDescription::fromString('original description');

        self::assertSame('original description', $description->description);
    }
}
