<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Unit\Authoring\Domain\Object\Skill\Workflow;

use Dairectiv\Authoring\Domain\Object\Skill\Workflow\SkillTemplate;
use Dairectiv\SharedKernel\Domain\Object\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class SkillTemplateTest extends TestCase
{
    public function testItShouldCreateTemplate(): void
    {
        $template = SkillTemplate::create('Entity', '<?php class Entity {}', 'A template for entities');

        self::assertSame('Entity', $template->name);
        self::assertSame('<?php class Entity {}', $template->content);
        self::assertSame('A template for entities', $template->description);
    }

    public function testItShouldCreateTemplateWithoutDescription(): void
    {
        $template = SkillTemplate::create('Entity', '<?php class Entity {}');

        self::assertNull($template->description);
    }

    public function testItShouldThrowExceptionWhenNameIsEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Template name cannot be empty.');

        SkillTemplate::create('', 'content');
    }

    public function testItShouldThrowExceptionWhenContentIsEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Template content cannot be empty.');

        SkillTemplate::create('name', '');
    }

    public function testItShouldConvertToArray(): void
    {
        $template = SkillTemplate::create('Entity', 'content', 'description');

        $array = $template->toArray();

        self::assertSame([
            'name'        => 'Entity',
            'content'     => 'content',
            'description' => 'description',
        ], $array);
    }

    public function testItShouldCreateFromArray(): void
    {
        $template = SkillTemplate::fromArray([
            'name'        => 'Entity',
            'content'     => 'content',
            'description' => 'description',
        ]);

        self::assertSame('Entity', $template->name);
        self::assertSame('content', $template->content);
        self::assertSame('description', $template->description);
    }

    public function testItShouldCreateFromArrayWithoutDescription(): void
    {
        $template = SkillTemplate::fromArray([
            'name'    => 'Entity',
            'content' => 'content',
        ]);

        self::assertNull($template->description);
    }
}
