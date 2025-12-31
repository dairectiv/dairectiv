<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\Authoring\UserInterface\Http\Api\Workflow;

use Cake\Chronos\Chronos;
use Dairectiv\Authoring\Domain\Object\Workflow\Example\Example;
use Dairectiv\Authoring\Domain\Object\Workflow\Step\Step;
use Dairectiv\Tests\Framework\IntegrationTestCase;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Response;

#[Group('integration')]
#[Group('authoring')]
#[Group('api')]
final class GetWorkflowTest extends IntegrationTestCase
{
    public function testItShouldGetWorkflowWithoutContentExamplesAndSteps(): void
    {
        $workflow = self::draftWorkflowEntity();
        $this->persistEntity($workflow);

        $this->getWorkflow();

        self::assertResponseIsSuccessful();

        IntegrationTestCase::assertResponseReturnsJson([
            'id'          => (string) $workflow->id,
            'name'        => $workflow->name,
            'description' => $workflow->description,
            'examples'    => [],
            'steps'       => [],
            'content'     => null,
            'state'       => 'draft',
            'updatedAt'   => Chronos::now()->toIso8601String(),
            'createdAt'   => Chronos::now()->toIso8601String(),
        ]);
    }

    public function testItShouldGetWorkflowWithContent(): void
    {
        $workflow = self::draftWorkflowEntity();
        $workflow->updateContent('Some workflow content');
        $this->persistEntity($workflow);

        $this->getWorkflow();

        self::assertResponseIsSuccessful();

        IntegrationTestCase::assertResponseReturnsJson([
            'id'          => (string) $workflow->id,
            'name'        => $workflow->name,
            'description' => $workflow->description,
            'examples'    => [],
            'steps'       => [],
            'content'     => 'Some workflow content',
            'state'       => 'draft',
            'updatedAt'   => Chronos::now()->toIso8601String(),
            'createdAt'   => Chronos::now()->toIso8601String(),
        ]);
    }

    public function testItShouldGetWorkflowWithExamples(): void
    {
        $workflow = self::draftWorkflowEntity();
        $example1 = Example::create($workflow, 'scenario1', 'input1', 'output1', 'explanation1');
        $example2 = Example::create($workflow, 'scenario2', 'input2', 'output2');
        $this->persistEntity($workflow);

        $this->getWorkflow();

        self::assertResponseIsSuccessful();

        IntegrationTestCase::assertResponseReturnsJson([
            'id'          => (string) $workflow->id,
            'name'        => $workflow->name,
            'description' => $workflow->description,
            'examples'    => [
                [
                    'id'          => $example1->id->toString(),
                    'scenario'    => 'scenario1',
                    'input'       => 'input1',
                    'output'      => 'output1',
                    'explanation' => 'explanation1',
                    'createdAt'   => Chronos::now()->toIso8601String(),
                    'updatedAt'   => Chronos::now()->toIso8601String(),
                ],
                [
                    'id'          => $example2->id->toString(),
                    'scenario'    => 'scenario2',
                    'input'       => 'input2',
                    'output'      => 'output2',
                    'explanation' => null,
                    'createdAt'   => Chronos::now()->toIso8601String(),
                    'updatedAt'   => Chronos::now()->toIso8601String(),
                ],
            ],
            'steps'     => [],
            'content'   => null,
            'state'     => 'draft',
            'updatedAt' => Chronos::now()->toIso8601String(),
            'createdAt' => Chronos::now()->toIso8601String(),
        ]);
    }

    public function testItShouldGetWorkflowWithSteps(): void
    {
        $workflow = self::draftWorkflowEntity();
        $step1 = Step::create($workflow, 'Step 1 content');
        $step2 = Step::create($workflow, 'Step 2 content', $step1);
        $this->persistEntity($workflow);

        $this->getWorkflow();

        self::assertResponseIsSuccessful();

        IntegrationTestCase::assertResponseReturnsJson([
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
            'state'     => 'draft',
            'updatedAt' => Chronos::now()->toIso8601String(),
            'createdAt' => Chronos::now()->toIso8601String(),
        ]);
    }

    public function testItShouldGetWorkflowWithContentExamplesAndSteps(): void
    {
        $workflow = self::draftWorkflowEntity();
        $workflow->updateContent('Complete workflow content');
        $example = Example::create($workflow, 'scenario', 'input', 'output', 'explanation');
        $step = Step::create($workflow, 'Step content');
        $this->persistEntity($workflow);

        $this->getWorkflow();

        self::assertResponseIsSuccessful();

        IntegrationTestCase::assertResponseReturnsJson([
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
            'steps' => [
                [
                    'id'        => $step->id->toString(),
                    'order'     => 1,
                    'content'   => 'Step content',
                    'createdAt' => Chronos::now()->toIso8601String(),
                    'updatedAt' => Chronos::now()->toIso8601String(),
                ],
            ],
            'content'   => 'Complete workflow content',
            'state'     => 'draft',
            'updatedAt' => Chronos::now()->toIso8601String(),
            'createdAt' => Chronos::now()->toIso8601String(),
        ]);
    }

    public function testItShouldGetPublishedWorkflow(): void
    {
        $workflow = self::draftWorkflowEntity();
        $workflow->publish();
        $this->persistEntity($workflow);

        $this->getWorkflow();

        self::assertResponseIsSuccessful();

        IntegrationTestCase::assertResponseReturnsJson([
            'id'          => (string) $workflow->id,
            'name'        => $workflow->name,
            'description' => $workflow->description,
            'examples'    => [],
            'steps'       => [],
            'content'     => null,
            'state'       => 'published',
            'updatedAt'   => Chronos::now()->toIso8601String(),
            'createdAt'   => Chronos::now()->toIso8601String(),
        ]);
    }

    public function testItShouldGetArchivedWorkflow(): void
    {
        $workflow = self::draftWorkflowEntity();
        $workflow->archive();
        $this->persistEntity($workflow);

        $this->getWorkflow();

        self::assertResponseIsSuccessful();

        IntegrationTestCase::assertResponseReturnsJson([
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
    }

    public function testItShouldReturn404WhenWorkflowNotFound(): void
    {
        $this->getWorkflow('non-existent-workflow');

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    private function getWorkflow(string $id = 'workflow-id'): void
    {
        $this->getJson(\sprintf('/api/authoring/workflows/%s', $id));
    }
}
