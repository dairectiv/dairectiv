<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Unit\Authoring\Domain\Object\Rule;

use Dairectiv\Authoring\Domain\Object\Rule\RuleContent;
use Dairectiv\SharedKernel\Domain\Object\Exception\InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
#[Group('authoring')]
final class RuleContentTest extends TestCase
{
    public function testItShouldCreateRuleContentFromValidString(): void
    {
        $content = RuleContent::fromString('## MUST\n- Use sprintf for formatting');

        self::assertSame('## MUST\n- Use sprintf for formatting', $content->content);
    }

    public function testItShouldReturnContentAsStringWhenCastToString(): void
    {
        $content = RuleContent::fromString('My rule content');

        self::assertSame('My rule content', (string) $content);
    }

    #[DataProvider('validContentsProvider')]
    public function testItShouldAcceptValidContents(string $value): void
    {
        $content = RuleContent::fromString($value);

        self::assertSame($value, $content->content);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function validContentsProvider(): iterable
    {
        yield 'simple content' => ['Simple rule content'];
        yield 'single character' => ['A'];
        yield 'markdown heading' => ['# Rule Title'];
        yield 'markdown list' => ["## MUST\n- Item 1\n- Item 2"];
        yield 'markdown code block' => ["```php\n\$foo = 'bar';\n```"];
        yield 'long content' => [str_repeat('a', 10000)];
    }

    public function testItShouldThrowExceptionWhenContentIsEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Rule content cannot be empty.');

        RuleContent::fromString('');
    }

    public function testItShouldAcceptMultilineMarkdownContent(): void
    {
        $markdown = <<<'MARKDOWN'
## Context
This rule applies to string formatting.

## MUST
- Always use `\sprintf()` for string formatting

## NEVER
- Never use string interpolation

## SHOULD
- Prefer named placeholders when possible
MARKDOWN;

        $content = RuleContent::fromString($markdown);

        self::assertStringContainsString('## MUST', $content->content);
        self::assertStringContainsString('## NEVER', $content->content);
    }

    public function testItShouldBeImmutable(): void
    {
        $content = RuleContent::fromString('original content');

        self::assertSame('original content', $content->content);
    }
}
