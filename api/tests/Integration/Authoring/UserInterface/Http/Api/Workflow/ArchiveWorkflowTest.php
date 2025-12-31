<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\Authoring\UserInterface\Http\Api\Workflow;

use Cake\Chronos\Chronos;
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
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        self::assertResponseReturnsJson([
            'id'          => (string) $workflow->id,
            'name'        => $workflow->name,
            'description' => $workflow->description,
            'examples'    => [],
            'steps'       => [],
            'content'     => null,
            'state'       => 'archived',
            'updatedAt'   => Chronos::now()->toIso8601String(),
            'createdAt'   => Chronos::now()->toIso8601String(),
        ]);

        self::assertDomainEventHasBeenDispatched(DirectiveArchived::class);
    }

    public function testItShouldArchivePublishedWorkflow(): void
    {
        $workflow = self::draftWorkflowEntity();
        $workflow->publish();
        $this->persistEntity($workflow);

        $this->archiveWorkflow((string) $workflow->id);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        self::assertResponseReturnsJson([
            'id'          => (string) $workflow->id,
            'name'        => $workflow->name,
            'description' => $workflow->description,
            'examples'    => [],
            'steps'       => [],
            'content'     => null,
            'state'       => 'archived',
            'updatedAt'   => Chronos::now()->toIso8601String(),
            'createdAt'   => Chronos::now()->toIso8601String(),
        ]);

        self::assertDomainEventHasBeenDispatched(DirectiveArchived::class);
    }

    public function testItShouldArchiveWorkflowWithContent(): void
    {
        $workflow = self::draftWorkflowEntity();
        $workflow->updateContent('Some workflow content');
        $this->persistEntity($workflow);

        $this->archiveWorkflow((string) $workflow->id);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        self::assertResponseReturnsJson([
            'id'          => (string) $workflow->id,
            'name'        => $workflow->name,
            'description' => $workflow->description,
            'examples'    => [],
            'steps'       => [],
            'content'     => 'Some workflow content',
            'state'       => 'archived',
            'updatedAt'   => Chronos::now()->toIso8601String(),
            'createdAt'   => Chronos::now()->toIso8601String(),
        ]);

        self::assertDomainEventHasBeenDispatched(DirectiveArchived::class);
    }

    public function testItShouldArchiveWorkflowWithExamples(): void
    {
        $workflow = self::draftWorkflowEntity();
        $example = Example::create($workflow, 'scenario', 'input', 'output', 'explanation');
        $this->persistEntity($workflow);

        $this->archiveWorkflow((string) $workflow->id);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        self::assertResponseReturnsJson([
            'id'          => (string) $workflow->id,
            'name'        => $workflow->name,
            'description' => $workflow->description,
            'examples'    => [
                [
                    'id'          => $example->id->toString(),
                    'scenario'    => 'scenario',
                    'input'       => 'input',
                    'output'      => 'output',
                    'explanation' => 'explanation',
                    'createdAt'   => Chronos::now()->toIso8601String(),
                    'updatedAt'   => Chronos::now()->toIso8601String(),
                ],
            ],
            'steps'     => [],
            'content'   => null,
            'state'     => 'archived',
            'updatedAt' => Chronos::now()->toIso8601String(),
            'createdAt' => Chronos::now()->toIso8601String(),
        ]);

        self::assertDomainEventHasBeenDispatched(DirectiveArchived::class);
    }

    public function testItShouldArchiveWorkflowWithSteps(): void
    {
        $workflow = self::draftWorkflowEntity();
        $step1 = Step::create($workflow, 'Step 1 content');
        $step2 = Step::create($workflow, 'Step 2 content', $step1);
        $this->persistEntity($workflow);

        $this->archiveWorkflow((string) $workflow->id);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        self::assertResponseReturnsJson([
            'id'          => (string) $workflow->id,
            'name'        => $workflow->name,
            'description' => $workflow->description,
            'examples'    => [],
            'steps'       => [
                [
                    'id'        => $step1->id->toString(),
                    'order'     => 1,
                    'content'   => 'Step 1 content',
                    'createdAt' => Chronos::now()->toIso8601String(),
                    'updatedAt' => Chronos::now()->toIso8601String(),
                ],
                [
                    'id'        => $step2->id->toString(),
                    'order'     => 2,
                    'content'   => 'Step 2 content',
                    'createdAt' => Chronos::now()->toIso8601String(),
                    'updatedAt' => Chronos::now()->toIso8601String(),
                ],
            ],
            'content'   => null,
            'state'     => 'archived',
            'updatedAt' => Chronos::now()->toIso8601String(),
            'createdAt' => Chronos::now()->toIso8601String(),
        ]);

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
        $this->postJson(\sprintf('/api/authoring/workflows/%s/archive', $id));
    }
}
