<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\Authoring\Application\Workflow\Example;

use Dairectiv\Authoring\Application\Workflow\Example\AddExample\Input;
use Dairectiv\Authoring\Application\Workflow\Example\AddExample\Output;
use Dairectiv\Authoring\Domain\Object\Directive\Event\DirectiveUpdated;
use Dairectiv\Authoring\Domain\Object\Workflow\Example\Example;
use Dairectiv\Authoring\Domain\Object\Workflow\Exception\WorkflowNotFoundException;
use Dairectiv\Authoring\Domain\Object\Workflow\Workflow;
use Dairectiv\SharedKernel\Domain\Object\Exception\InvalidArgumentException;
use Dairectiv\Tests\Framework\IntegrationTestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('integration')]
#[Group('authoring')]
#[Group('use-case')]
final class AddExampleTest extends IntegrationTestCase
{
    public function testItShouldAddExampleToWorkflow(): void
    {
        $workflow = self::draftWorkflowEntity();
        $this->persistEntity($workflow);

        $output = $this->executeAddExample(
            (string) $workflow->id,
            'Test scenario',
            'Test input',
            'Test output',
            'Test explanation',
        );

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $example = $output->example;

        self::assertSame('Test scenario', $example->scenario);
        self::assertSame('Test input', $example->input);
        self::assertSame('Test output', $example->output);
        self::assertSame('Test explanation', $example->explanation);

        // Verify persistence
        $persistedWorkflow = $this->findEntity(Workflow::class, ['id' => $workflow->id], true);

        self::assertCount(1, $persistedWorkflow->examples);

        $persistedExample = $persistedWorkflow->examples->first();

        self::assertInstanceOf(Example::class, $persistedExample);
        self::assertSame('Test scenario', $persistedExample->scenario);
        self::assertSame('Test input', $persistedExample->input);
        self::assertSame('Test output', $persistedExample->output);
        self::assertSame('Test explanation', $persistedExample->explanation);
    }

    public function testItShouldAddExampleWithoutExplanation(): void
    {
        $workflow = self::draftWorkflowEntity();
        $this->persistEntity($workflow);

        $output = $this->executeAddExample(
            (string) $workflow->id,
            'Test scenario',
            'Test input',
            'Test output',
        );

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        self::assertNull($output->example->explanation);

        $persistedWorkflow = $this->findEntity(Workflow::class, ['id' => $workflow->id], true);
        $persistedExample = $persistedWorkflow->examples->first();

        self::assertInstanceOf(Example::class, $persistedExample);
        self::assertNull($persistedExample->explanation);
    }

    public function testItShouldAddMultipleExamplesToWorkflow(): void
    {
        $workflow = self::draftWorkflowEntity();
        $this->persistEntity($workflow);

        $this->executeAddExample((string) $workflow->id, 'Scenario 1', 'Input 1', 'Output 1');
        $this->executeAddExample((string) $workflow->id, 'Scenario 2', 'Input 2', 'Output 2');
        $this->executeAddExample((string) $workflow->id, 'Scenario 3', 'Input 3', 'Output 3');

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class, 3);

        $persistedWorkflow = $this->findEntity(Workflow::class, ['id' => $workflow->id], true);

        self::assertCount(3, $persistedWorkflow->examples);
    }

    public function testItShouldThrowExceptionWhenWorkflowNotFound(): void
    {
        $this->expectException(WorkflowNotFoundException::class);

        $this->executeAddExample('non-existent-workflow', 'Scenario', 'Input', 'Output');
    }

    public function testItShouldThrowExceptionWhenWorkflowIsArchived(): void
    {
        $workflow = self::draftWorkflowEntity();
        $workflow->archive();
        $this->persistEntity($workflow);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot perform this action on an archived directive.');

        $this->executeAddExample((string) $workflow->id, 'Scenario', 'Input', 'Output');
    }

    public function testItShouldAddExampleToPublishedWorkflow(): void
    {
        $workflow = self::draftWorkflowEntity();
        $workflow->publish();
        $this->persistEntity($workflow);

        $output = $this->executeAddExample(
            (string) $workflow->id,
            'Test scenario',
            'Test input',
            'Test output',
        );

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);
        self::assertSame('Test scenario', $output->example->scenario);
    }

    private function executeAddExample(
        string $workflowId,
        string $scenario,
        string $input,
        string $output,
        ?string $explanation = null,
    ): Output {
        $result = $this->execute(new Input($workflowId, $scenario, $input, $output, $explanation));

        self::assertInstanceOf(Output::class, $result);

        return $result;
    }
}
