<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\Authoring\Application\Directive;

use Dairectiv\Authoring\Application\Directive\ArchiveDirective\Input;
use Dairectiv\Authoring\Domain\Object\Directive\DirectiveState;
use Dairectiv\Authoring\Domain\Object\Directive\Event\DirectiveArchived;
use Dairectiv\Authoring\Domain\Object\Directive\Exception\DirectiveNotFoundException;
use Dairectiv\Authoring\Domain\Object\Rule\Rule;
use Dairectiv\Authoring\Domain\Object\Workflow\Workflow;
use Dairectiv\Tests\Framework\IntegrationTestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('integration')]
#[Group('authoring')]
#[Group('use-case')]
final class ArchiveDirectiveTest extends IntegrationTestCase
{
    public function testItShouldArchiveRule(): void
    {
        $rule = self::draftRuleEntity();
        $this->persistEntity($rule);

        $this->execute(new Input((string) $rule->id));

        self::assertDomainEventHasBeenDispatched(DirectiveArchived::class);
        $rule = $this->findEntity(Rule::class, ['id' => $rule->id], true);

        self::assertSame(DirectiveState::Archived, $rule->state);
    }

    public function testItShouldArchiveWorkflow(): void
    {
        $workflow = self::draftWorkflowEntity();
        $this->persistEntity($workflow);

        $this->execute(new Input((string) $workflow->id));

        self::assertDomainEventHasBeenDispatched(DirectiveArchived::class);
        $workflow = $this->findEntity(Workflow::class, ['id' => $workflow->id], true);

        self::assertSame(DirectiveState::Archived, $workflow->state);
    }

    public function testItShouldThrowExceptionWhenDirectiveNotFound(): void
    {
        $this->expectException(DirectiveNotFoundException::class);

        $this->execute(new Input('non-existent-directive'));
    }
}
