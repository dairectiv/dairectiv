<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\Authoring\UserInterface\Http\Api\Workflow;

use Dairectiv\Authoring\Domain\Object\Directive\Event\DirectiveUpdated;
use Dairectiv\Authoring\Domain\Object\Workflow\Example\Example;
use Dairectiv\Authoring\Domain\Object\Workflow\Workflow;
use Dairectiv\SharedKernel\Domain\Object\Event\DomainEventQueue;
use Dairectiv\Tests\Framework\IntegrationTestCase;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Response;

#[Group('integration')]
#[Group('authoring')]
#[Group('api')]
final class RemoveWorkflowExampleTest extends IntegrationTestCase
{
    public function testItShouldRemoveExample(): void
    {
        $workflow = self::draftWorkflowEntity();
        $example = Example::create($workflow, 'Scenario', 'Input', 'Output', 'Explanation');
        $this->persistEntity($workflow);

        self::assertCount(1, $workflow->examples);

        $this->removeExample((string) $workflow->id, $example->id->toString());

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $persistedWorkflow = $this->findEntity(Workflow::class, ['id' => $workflow->id], true);

        self::assertCount(0, $persistedWorkflow->examples);
    }

    public function testItShouldRemoveOneExampleFromMultiple(): void
    {
        $workflow = self::draftWorkflowEntity();
        Example::create($workflow, 'Scenario 1', 'Input 1', 'Output 1');
        $example2 = Example::create($workflow, 'Scenario 2', 'Input 2', 'Output 2');
        Example::create($workflow, 'Scenario 3', 'Input 3', 'Output 3');
        $this->persistEntity($workflow);

        self::assertCount(3, $workflow->examples);

        $this->removeExample((string) $workflow->id, $example2->id->toString());

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $persistedWorkflow = $this->findEntity(Workflow::class, ['id' => $workflow->id], true);

        self::assertCount(2, $persistedWorkflow->examples);

        $remainingScenarios = $persistedWorkflow->examples->map(static fn ($e) => $e->scenario)->toArray();
        self::assertContains('Scenario 1', $remainingScenarios);
        self::assertContains('Scenario 3', $remainingScenarios);
        self::assertNotContains('Scenario 2', $remainingScenarios);
    }

    public function testItShouldReturn404WhenWorkflowNotFound(): void
    {
        $this->removeExample('non-existent-workflow', '00000000-0000-0000-0000-000000000000');

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testItShouldReturn404WhenExampleNotFound(): void
    {
        $workflow = self::draftWorkflowEntity();
        $this->persistEntity($workflow);

        $this->removeExample((string) $workflow->id, '00000000-0000-0000-0000-000000000000');

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testItShouldReturn404WhenWorkflowIsArchived(): void
    {
        $workflow = self::draftWorkflowEntity();
        $example = Example::create($workflow, 'Scenario', 'Input', 'Output');
        $workflow->archive();
        $this->persistEntity($workflow);

        $this->removeExample((string) $workflow->id, $example->id->toString());

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    private function removeExample(string $workflowId, string $exampleId): void
    {
        DomainEventQueue::reset();
        $this->deleteJson(\sprintf('/api/authoring/workflows/%s/examples/%s', $workflowId, $exampleId));
    }
}
