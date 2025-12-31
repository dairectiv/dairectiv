<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\Authoring\UserInterface\Http\Api\Workflow;

use Dairectiv\Authoring\Domain\Object\Directive\Event\DirectivePublished;
use Dairectiv\Authoring\Domain\Object\Workflow\Example\Example;
use Dairectiv\Authoring\Domain\Object\Workflow\Step\Step;
use Dairectiv\SharedKernel\Domain\Object\Event\DomainEventQueue;
use Dairectiv\Tests\Framework\IntegrationTestCase;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Response;

#[Group('integration')]
#[Group('authoring')]
#[Group('api')]
final class PublishWorkflowTest extends IntegrationTestCase
{
    public function testItShouldPublishDraftWorkflow(): void
    {
        $workflow = self::draftWorkflowEntity();
        $this->persistEntity($workflow);

        $this->publishWorkflow((string) $workflow->id);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        self::assertDomainEventHasBeenDispatched(DirectivePublished::class);
    }

    public function testItShouldPublishWorkflowWithContent(): void
    {
        $workflow = self::draftWorkflowEntity();
        $workflow->updateContent('Some workflow content');
        $this->persistEntity($workflow);

        $this->publishWorkflow((string) $workflow->id);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        self::assertDomainEventHasBeenDispatched(DirectivePublished::class);
    }

    public function testItShouldPublishWorkflowWithExamples(): void
    {
        $workflow = self::draftWorkflowEntity();
        Example::create($workflow, 'scenario', 'input', 'output', 'explanation');
        $this->persistEntity($workflow);

        $this->publishWorkflow((string) $workflow->id);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        self::assertDomainEventHasBeenDispatched(DirectivePublished::class);
    }

    public function testItShouldPublishWorkflowWithSteps(): void
    {
        $workflow = self::draftWorkflowEntity();
        $step1 = Step::create($workflow, 'Step 1 content');
        Step::create($workflow, 'Step 2 content', $step1);
        $this->persistEntity($workflow);

        $this->publishWorkflow((string) $workflow->id);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        self::assertDomainEventHasBeenDispatched(DirectivePublished::class);
    }

    public function testItShouldReturn404WhenWorkflowNotFound(): void
    {
        $this->publishWorkflow('non-existent-workflow');

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testItShouldReturn404WhenRuleIdProvided(): void
    {
        $rule = self::draftRuleEntity();
        $this->persistEntity($rule);

        $this->publishWorkflow((string) $rule->id);

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testItShouldReturn409WhenWorkflowAlreadyPublished(): void
    {
        $workflow = self::draftWorkflowEntity();
        $workflow->publish();
        $this->persistEntity($workflow);

        $this->publishWorkflow((string) $workflow->id);

        self::assertResponseStatusCodeSame(Response::HTTP_CONFLICT);
    }

    public function testItShouldReturn409WhenWorkflowIsArchived(): void
    {
        $workflow = self::draftWorkflowEntity();
        $workflow->archive();
        $this->persistEntity($workflow);

        $this->publishWorkflow((string) $workflow->id);

        self::assertResponseStatusCodeSame(Response::HTTP_CONFLICT);
    }

    private function publishWorkflow(string $id): void
    {
        DomainEventQueue::reset();
        $this->putJson(\sprintf('/api/authoring/workflows/%s/publish', $id));
    }
}
