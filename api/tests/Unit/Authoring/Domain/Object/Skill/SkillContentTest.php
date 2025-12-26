<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Unit\Authoring\Domain\Object\Skill;

use Dairectiv\Authoring\Domain\Object\Skill\SkillContent;
use Dairectiv\SharedKernel\Domain\Object\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class SkillContentTest extends TestCase
{
    public function testItShouldCreateSkillContent(): void
    {
        $content = SkillContent::fromString('## When to Use\nUse this skill when...');

        self::assertSame('## When to Use\nUse this skill when...', $content->content);
        self::assertSame('## When to Use\nUse this skill when...', (string) $content);
    }

    public function testItShouldThrowExceptionWhenContentIsEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Skill content cannot be empty.');

        SkillContent::fromString('');
    }
}
