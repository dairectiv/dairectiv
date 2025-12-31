<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\Authoring\UserInterface\Http\Api\Workflow;

use Dairectiv\Authoring\Domain\Object\Directive\Event\DirectiveArchived;
use Dairectiv\Authoring\Domain\Object\Workflow\Example\Example;
use Dairectiv\Authoring\Domain\Object\Workflow\Step\Step;
use Dairectiv\SharedKernel\Domain\Object\Event\DomainEventQueue;
use Dairectiv\Tests\Framework\IntegrationTestCase;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Response;

#[Group('integration')]
#[Group('authoring')]
#[Group('api')]
final class ArchiveWorkflowTest extends IntegrationTestCase
{
    public function testItShouldArchiveDraftWorkflow(): void
    {
        $workflow = self::draftWorkflowEntity();
        $this->persistEntity($workflow);

        $this->archiveWorkflow((string) $workflow->id);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        self::assertDomainEventHasBeenDispatched(DirectiveArchived::class);
    }

    public function testItShouldArchivePublishedWorkflow(): void
    {
        $workflow = self::draftWorkflowEntity();
        $workflow->publish();
        $this->persistEntity($workflow);

        $this->archiveWorkflow((string) $workflow->id);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        self::assertDomainEventHasBeenDispatched(DirectiveArchived::class);
    }

    public function testItShouldArchiveWorkflowWithContent(): void
    {
        $workflow = self::draftWorkflowEntity();
        $workflow->updateContent('Some workflow content');
        $this->persistEntity($workflow);

        $this->archiveWorkflow((string) $workflow->id);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        self::assertDomainEventHasBeenDispatched(DirectiveArchived::class);
    }

    public function testItShouldArchiveWorkflowWithExamples(): void
    {
        $workflow = self::draftWorkflowEntity();
        Example::create($workflow, 'scenario', 'input', 'output', 'explanation');
        $this->persistEntity($workflow);

        $this->archiveWorkflow((string) $workflow->id);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        self::assertDomainEventHasBeenDispatched(DirectiveArchived::class);
    }

    public function testItShouldArchiveWorkflowWithSteps(): void
    {
        $workflow = self::draftWorkflowEntity();
        $step1 = Step::create($workflow, 'Step 1 content');
        Step::create($workflow, 'Step 2 content', $step1);
        $this->persistEntity($workflow);

        $this->archiveWorkflow((string) $workflow->id);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        self::assertDomainEventHasBeenDispatched(DirectiveArchived::class);
    }

    public function testItShouldReturn404WhenWorkflowNotFound(): void
    {
        $this->archiveWorkflow('non-existent-workflow');

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testItShouldReturn404WhenRuleIdProvided(): void
    {
        $rule = self::draftRuleEntity();
        $this->persistEntity($rule);

        $this->archiveWorkflow((string) $rule->id);

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testItShouldReturn409WhenWorkflowAlreadyArchived(): void
    {
        $workflow = self::draftWorkflowEntity();
        $workflow->archive();
        $this->persistEntity($workflow);

        $this->archiveWorkflow((string) $workflow->id);

        self::assertResponseStatusCodeSame(Response::HTTP_CONFLICT);
    }

    private function archiveWorkflow(string $id): void
    {
        DomainEventQueue::reset();
        $this->putJson(\sprintf('/api/authoring/workflows/%s/archive', $id));
    }
}
