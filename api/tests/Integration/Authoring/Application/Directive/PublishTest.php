<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\Authoring\Application\Directive;

use Dairectiv\Authoring\Application\Directive\Publish\Input;
use Dairectiv\Authoring\Domain\Object\Directive\DirectiveState;
use Dairectiv\Authoring\Domain\Object\Directive\Event\DirectivePublished;
use Dairectiv\Authoring\Domain\Object\Directive\Exception\DirectiveNotFoundException;
use Dairectiv\Authoring\Domain\Object\Rule\Rule;
use Dairectiv\Authoring\Domain\Object\Skill\Skill;
use Dairectiv\Tests\Framework\IntegrationTestCase;
use PHPUnit\Framework\Attributes\Group;
use Zenstruck\Foundry\Test\Factories;

#[Group('integration')]
#[Group('authoring')]
#[Group('use-case')]
final class PublishTest extends IntegrationTestCase
{
    use Factories;

    public function testItShouldPublishRule(): void
    {
        $rule = self::draftRule();
        $this->persistEntity($rule);

        $this->execute(new Input((string) $rule->id));

        self::assertDomainEventHasBeenDispatched(DirectivePublished::class);
        $rule = $this->findEntity(Rule::class, ['id' => $rule->id], true);

        self::assertSame(DirectiveState::Published, $rule->state);
    }

    public function testItShouldPublishSkill(): void
    {
        $skill = self::draftSkill();
        $this->persistEntity($skill);

        $this->execute(new Input((string) $skill->id));

        self::assertDomainEventHasBeenDispatched(DirectivePublished::class);
        $skill = $this->findEntity(Skill::class, ['id' => $skill->id], true);

        self::assertSame(DirectiveState::Published, $skill->state);
    }

    public function testItShouldThrowExceptionWhenDirectiveNotFound(): void
    {
        $this->expectException(DirectiveNotFoundException::class);

        $this->execute(new Input('non-existent-directive'));
    }
}
