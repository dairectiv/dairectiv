<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\Authoring\Application\Directive;

use Dairectiv\Authoring\Application\Directive\DeleteDirective\Input;
use Dairectiv\Authoring\Domain\Object\Directive\Event\DirectiveDeleted;
use Dairectiv\Authoring\Domain\Object\Directive\Exception\DirectiveNotFoundException;
use Dairectiv\Authoring\Domain\Object\Rule\Exception\RuleNotFoundException;
use Dairectiv\Authoring\Domain\Object\Rule\Rule;
use Dairectiv\Authoring\Domain\Object\Workflow\Exception\WorkflowNotFoundException;
use Dairectiv\Authoring\Domain\Object\Workflow\Workflow;
use Dairectiv\Authoring\Domain\Repository\RuleRepository;
use Dairectiv\Authoring\Domain\Repository\WorkflowRepository;
use Dairectiv\Tests\Framework\IntegrationTestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('integration')]
#[Group('authoring')]
#[Group('use-case')]
final class DeleteDirectiveTest extends IntegrationTestCase
{
    public function testItShouldDeleteRule(): void
    {
        $rule = self::draftRuleEntity();
        $originalId = $rule->id;
        $this->persistEntity($rule);

        $this->execute(new Input((string) $originalId));

        self::assertDomainEventHasBeenDispatched(DirectiveDeleted::class);

        // After delete, the old ID should not be found (soft delete filter)
        $deletedRule = $this->findEntity(Rule::class, ['id' => $originalId]);
        self::assertNull($deletedRule);
    }

    public function testItShouldDeleteWorkflow(): void
    {
        $workflow = self::draftWorkflowEntity();
        $originalId = $workflow->id;
        $this->persistEntity($workflow);

        $this->execute(new Input((string) $originalId));

        self::assertDomainEventHasBeenDispatched(DirectiveDeleted::class);

        // After delete, the old ID should not be found (soft delete filter)
        $deletedWorkflow = $this->findEntity(Workflow::class, ['id' => $originalId]);
        self::assertNull($deletedWorkflow);
    }

    public function testItShouldDeleteArchivedRule(): void
    {
        $rule = self::draftRuleEntity();
        $rule->archive();
        $archivedId = $rule->id;
        $this->persistEntity($rule);

        $this->execute(new Input((string) $archivedId));

        self::assertDomainEventHasBeenDispatched(DirectiveDeleted::class);
    }

    public function testItShouldThrowExceptionWhenDirectiveNotFound(): void
    {
        $this->expectException(DirectiveNotFoundException::class);

        $this->execute(new Input('non-existent-directive'));
    }

    public function testItShouldThrowExceptionWhenGettingDeletedRule(): void
    {
        $rule = self::draftRuleEntity();
        $originalId = $rule->id;
        $this->persistEntity($rule);

        $this->execute(new Input((string) $originalId));

        self::assertDomainEventHasBeenDispatched(DirectiveDeleted::class);

        // Clear entity manager to force fresh fetch
        $this->getEntityManager()->clear();

        // Trying to get the deleted rule via repository should throw
        $this->expectException(RuleNotFoundException::class);
        self::getService(RuleRepository::class)->getRuleById($originalId);
    }

    public function testItShouldThrowExceptionWhenGettingDeletedWorkflow(): void
    {
        $workflow = self::draftWorkflowEntity();
        $originalId = $workflow->id;
        $this->persistEntity($workflow);

        $this->execute(new Input((string) $originalId));

        self::assertDomainEventHasBeenDispatched(DirectiveDeleted::class);

        // Clear entity manager to force fresh fetch
        $this->getEntityManager()->clear();

        // Trying to get the deleted workflow via repository should throw
        $this->expectException(WorkflowNotFoundException::class);
        self::getService(WorkflowRepository::class)->getWorkflowById($originalId);
    }
}
