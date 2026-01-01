<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\Authoring\UserInterface\Http\Api\Workflow;

use Dairectiv\Authoring\Domain\Object\Directive\Event\DirectiveUpdated;
use Dairectiv\Authoring\Domain\Object\Workflow\Example\Example;
use Dairectiv\SharedKernel\Domain\Object\Event\DomainEventQueue;
use Dairectiv\Tests\Framework\IntegrationTestCase;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Response;

#[Group('integration')]
#[Group('authoring')]
#[Group('api')]
final class UpdateWorkflowExampleTest extends IntegrationTestCase
{
    public function testItShouldUpdateAllExampleFields(): void
    {
        $workflow = self::draftWorkflowEntity();
        $example = Example::create($workflow, 'Original scenario', 'Original input', 'Original output', 'Original explanation');
        $this->persistEntity($workflow);

        $this->updateExample((string) $workflow->id, $example->id->toString(), [
            'scenario'    => 'Updated scenario',
            'input'       => 'Updated input',
            'output'      => 'Updated output',
            'explanation' => 'Updated explanation',
        ]);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);
    }

    public function testItShouldClearExplanation(): void
    {
        $workflow = self::draftWorkflowEntity();
        $example = Example::create($workflow, 'Original scenario', 'Original input', 'Original output', 'Original explanation');
        $this->persistEntity($workflow);

        $this->updateExample((string) $workflow->id, $example->id->toString(), [
            'scenario'    => 'Updated scenario',
            'input'       => 'Updated input',
            'output'      => 'Updated output',
            'explanation' => null,
        ]);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);
    }

    public function testItShouldReturn404WhenWorkflowNotFound(): void
    {
        $this->updateExample('non-existent-workflow', '00000000-0000-0000-0000-000000000000', [
            'scenario' => 'Updated scenario',
            'input'    => 'Updated input',
            'output'   => 'Updated output',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testItShouldReturn400WhenExampleNotFound(): void
    {
        $workflow = self::draftWorkflowEntity();
        $this->persistEntity($workflow);

        $this->updateExample((string) $workflow->id, '00000000-0000-0000-0000-000000000000', [
            'scenario' => 'Updated scenario',
            'input'    => 'Updated input',
            'output'   => 'Updated output',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testItShouldReturn422WhenMissingRequiredFields(): void
    {
        $workflow = self::draftWorkflowEntity();
        $example = Example::create($workflow, 'Scenario', 'Input', 'Output');
        $this->persistEntity($workflow);

        $this->updateExample((string) $workflow->id, $example->id->toString(), []);

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testItShouldReturn400WhenWorkflowIsArchived(): void
    {
        $workflow = self::draftWorkflowEntity();
        $example = Example::create($workflow, 'Scenario', 'Input', 'Output');
        $workflow->archive();
        $this->persistEntity($workflow);

        $this->updateExample((string) $workflow->id, $example->id->toString(), [
            'scenario' => 'Updated scenario',
            'input'    => 'Updated input',
            'output'   => 'Updated output',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function updateExample(string $workflowId, string $exampleId, array $payload): void
    {
        DomainEventQueue::reset();
        $this->putJson(\sprintf('/api/authoring/workflows/%s/examples/%s', $workflowId, $exampleId), $payload);
    }
}
