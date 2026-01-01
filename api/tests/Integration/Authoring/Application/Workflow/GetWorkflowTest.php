<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\Authoring\Application\Workflow;

use Dairectiv\Authoring\Application\Workflow\GetWorkflow\Input;
use Dairectiv\Authoring\Application\Workflow\GetWorkflow\Output;
use Dairectiv\Authoring\Domain\Object\Directive\DirectiveState;
use Dairectiv\Authoring\Domain\Object\Workflow\Example\Example;
use Dairectiv\Authoring\Domain\Object\Workflow\Exception\WorkflowNotFoundException;
use Dairectiv\Authoring\Domain\Object\Workflow\Step\Step;
use Dairectiv\Tests\Framework\IntegrationTestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('integration')]
#[Group('authoring')]
#[Group('use-case')]
final class GetWorkflowTest extends IntegrationTestCase
{
    public function testItShouldGetWorkflow(): void
    {
        $workflow = self::draftWorkflowEntity(id: 'my-workflow', name: 'My Workflow', description: 'A description');
        $workflow->updateContent('Some content');
        $this->persistEntity($workflow);

        $output = $this->executeGetWorkflow('my-workflow');

        self::assertSame('my-workflow', (string) $output->workflow->id);
        self::assertSame('My Workflow', $output->workflow->name);
        self::assertSame('A description', $output->workflow->description);
        self::assertSame('Some content', $output->workflow->content);
        self::assertSame(DirectiveState::Draft, $output->workflow->state);
    }

    public function testItShouldGetWorkflowWithExamples(): void
    {
        $workflow = self::draftWorkflowEntity(id: 'workflow-with-examples');
        Example::create($workflow, 'Scenario 1', 'Input 1', 'Output 1', 'Explanation 1');
        Example::create($workflow, 'Scenario 2', 'Input 2', 'Output 2', 'Explanation 2');
        $this->persistEntity($workflow);

        $output = $this->executeGetWorkflow('workflow-with-examples');

        self::assertCount(2, $output->workflow->examples);
    }

    public function testItShouldGetWorkflowWithSteps(): void
    {
        $workflow = self::draftWorkflowEntity(id: 'workflow-with-steps');
        Step::create($workflow, 'Step 1 content');
        Step::create($workflow, 'Step 2 content');
        Step::create($workflow, 'Step 3 content');
        $this->persistEntity($workflow);

        $output = $this->executeGetWorkflow('workflow-with-steps');

        self::assertCount(3, $output->workflow->steps);
    }

    public function testItShouldThrowExceptionWhenWorkflowNotFound(): void
    {
        $this->expectException(WorkflowNotFoundException::class);
        $this->expectExceptionMessage('Workflow with ID non-existent-workflow not found.');

        $this->executeGetWorkflow('non-existent-workflow');
    }

    public function testItShouldThrowExceptionWhenWorkflowIsDeleted(): void
    {
        $workflow = self::draftWorkflowEntity(id: 'deleted-workflow');
        $workflow->delete();
        $this->persistEntity($workflow);

        $this->expectException(WorkflowNotFoundException::class);

        $this->executeGetWorkflow('deleted-workflow');
    }

    private function executeGetWorkflow(string $id): Output
    {
        $output = $this->fetch(new Input($id));

        self::assertInstanceOf(Output::class, $output);

        return $output;
    }
}
