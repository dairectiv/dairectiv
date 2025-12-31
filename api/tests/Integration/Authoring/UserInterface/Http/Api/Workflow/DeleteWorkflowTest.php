<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\Authoring\UserInterface\Http\Api\Workflow;

use Dairectiv\Authoring\Domain\Object\Directive\Event\DirectiveDeleted;
use Dairectiv\Authoring\Domain\Object\Workflow\Example\Example;
use Dairectiv\Authoring\Domain\Object\Workflow\Step\Step;
use Dairectiv\SharedKernel\Domain\Object\Event\DomainEventQueue;
use Dairectiv\Tests\Framework\IntegrationTestCase;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Response;

#[Group('integration')]
#[Group('authoring')]
#[Group('api')]
final class DeleteWorkflowTest extends IntegrationTestCase
{
    public function testItShouldDeleteDraftWorkflow(): void
    {
        $workflow = self::draftWorkflowEntity();
        $this->persistEntity($workflow);

        $this->deleteWorkflow((string) $workflow->id);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        self::assertDomainEventHasBeenDispatched(DirectiveDeleted::class);
    }

    public function testItShouldDeletePublishedWorkflow(): void
    {
        $workflow = self::draftWorkflowEntity();
        $workflow->publish();
        $this->persistEntity($workflow);

        $this->deleteWorkflow((string) $workflow->id);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        self::assertDomainEventHasBeenDispatched(DirectiveDeleted::class);
    }

    public function testItShouldDeleteArchivedWorkflow(): void
    {
        $workflow = self::draftWorkflowEntity();
        $workflow->archive();
        $this->persistEntity($workflow);

        $this->deleteWorkflow((string) $workflow->id);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        self::assertDomainEventHasBeenDispatched(DirectiveDeleted::class);
    }

    public function testItShouldDeleteWorkflowWithContent(): void
    {
        $workflow = self::draftWorkflowEntity();
        $workflow->updateContent('Some workflow content');
        $this->persistEntity($workflow);

        $this->deleteWorkflow((string) $workflow->id);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        self::assertDomainEventHasBeenDispatched(DirectiveDeleted::class);
    }

    public function testItShouldDeleteWorkflowWithExamples(): void
    {
        $workflow = self::draftWorkflowEntity();
        Example::create($workflow, 'scenario', 'input', 'output', 'explanation');
        $this->persistEntity($workflow);

        $this->deleteWorkflow((string) $workflow->id);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        self::assertDomainEventHasBeenDispatched(DirectiveDeleted::class);
    }

    public function testItShouldDeleteWorkflowWithSteps(): void
    {
        $workflow = self::draftWorkflowEntity();
        Step::create($workflow, 'Step 1 content');
        $this->persistEntity($workflow);

        $this->deleteWorkflow((string) $workflow->id);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        self::assertDomainEventHasBeenDispatched(DirectiveDeleted::class);
    }

    public function testItShouldReturn404WhenWorkflowNotFound(): void
    {
        $this->deleteWorkflow('non-existent-workflow');

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testItShouldReturn404WhenRuleIdProvided(): void
    {
        $rule = self::draftRuleEntity();
        $this->persistEntity($rule);

        $this->deleteWorkflow((string) $rule->id);

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testItShouldReturn404WhenWorkflowAlreadyDeleted(): void
    {
        $workflow = self::draftWorkflowEntity();
        $workflow->delete();
        $this->persistEntity($workflow);

        // The soft delete filter excludes deleted directives, so we get 404
        $this->deleteWorkflow('workflow-id');

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    private function deleteWorkflow(string $id): void
    {
        DomainEventQueue::reset();
        $this->deleteJson(\sprintf('/api/authoring/workflows/%s', $id));
    }
}
