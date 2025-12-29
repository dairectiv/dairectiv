<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\Authoring\Application\Directive;

use Dairectiv\Authoring\Application\Directive\Archive\Input;
use Dairectiv\Authoring\Domain\Object\Directive\DirectiveId;
use Dairectiv\Authoring\Domain\Object\Directive\DirectiveState;
use Dairectiv\Authoring\Domain\Object\Directive\Event\DirectiveArchived;
use Dairectiv\Authoring\Domain\Object\Directive\Exception\DirectiveNotFoundException;
use Dairectiv\Authoring\Domain\Object\Rule\Rule;
use Dairectiv\Authoring\Domain\Object\Skill\Skill;
use Dairectiv\Authoring\Infrastructure\Zenstruck\Foundry\Factory\Rule\RuleFactory;
use Dairectiv\Authoring\Infrastructure\Zenstruck\Foundry\Factory\Skill\SkillFactory;
use Dairectiv\Tests\Framework\IntegrationTestCase;
use PHPUnit\Framework\Attributes\Group;
use Zenstruck\Foundry\Test\Factories;

#[Group('integration')]
#[Group('authoring')]
#[Group('use-case')]
final class ArchiveTest extends IntegrationTestCase
{
    use Factories;

    public function testItShouldArchiveRule(): void
    {
        $rule = RuleFactory::new()->withId('rule-to-archive')->create();

        $this->execute(new Input($rule->id));

        self::assertDomainEventHasBeenDispatched(DirectiveArchived::class);
        $rule = $this->findEntity(Rule::class, ['id' => DirectiveId::fromString($rule->id)], true);

        self::assertSame(DirectiveState::Archived, $rule->state);
    }

    public function testItShouldArchiveSkill(): void
    {
        $skill = SkillFactory::createOne();

        $this->execute(new Input($skill->id));

        self::assertDomainEventHasBeenDispatched(DirectiveArchived::class);
        $skill = $this->findEntity(Skill::class, ['id' => DirectiveId::fromString($skill->id)], true);

        self::assertSame(DirectiveState::Archived, $skill->state);
    }

    public function testItShouldThrowExceptionWhenDirectiveNotFound(): void
    {
        $this->expectException(DirectiveNotFoundException::class);

        $this->execute(new Input('non-existent-directive'));
    }
}
