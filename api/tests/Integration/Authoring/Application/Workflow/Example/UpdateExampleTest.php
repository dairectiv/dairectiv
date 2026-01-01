<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\Authoring\Application\Workflow\Example;

use Dairectiv\Authoring\Application\Workflow\Example\UpdateExample\Input;
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
final class UpdateExampleTest extends IntegrationTestCase
{
    public function testItShouldUpdateAllExampleFields(): void
    {
        $workflow = self::draftWorkflowEntity();
        $example = Example::create($workflow, 'Original scenario', 'Original input', 'Original output', 'Original explanation');
        $this->persistEntity($workflow);

        $this->execute(new Input(
            (string) $workflow->id,
            (string) $example->id,
            scenario: 'Updated scenario',
            input: 'Updated input',
            output: 'Updated output',
            explanation: 'Updated explanation',
        ));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $persistedWorkflow = $this->findEntity(Workflow::class, ['id' => $workflow->id], true);
        $persistedExample = $persistedWorkflow->examples->first();

        self::assertInstanceOf(Example::class, $persistedExample);
        self::assertSame('Updated scenario', $persistedExample->scenario);
        self::assertSame('Updated input', $persistedExample->input);
        self::assertSame('Updated output', $persistedExample->output);
        self::assertSame('Updated explanation', $persistedExample->explanation);
    }

    public function testItShouldUpdateScenarioOnly(): void
    {
        $workflow = self::draftWorkflowEntity();
        $example = Example::create($workflow, 'Original scenario', 'Original input', 'Original output', 'Original explanation');
        $this->persistEntity($workflow);

        $this->execute(new Input(
            (string) $workflow->id,
            (string) $example->id,
            scenario: 'Updated scenario',
        ));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $persistedWorkflow = $this->findEntity(Workflow::class, ['id' => $workflow->id], true);
        $persistedExample = $persistedWorkflow->examples->first();

        self::assertInstanceOf(Example::class, $persistedExample);
        self::assertSame('Updated scenario', $persistedExample->scenario);
        self::assertSame('Original input', $persistedExample->input);
        self::assertSame('Original output', $persistedExample->output);
        self::assertSame('Original explanation', $persistedExample->explanation);
    }

    public function testItShouldUpdateInputOnly(): void
    {
        $workflow = self::draftWorkflowEntity();
        $example = Example::create($workflow, 'Original scenario', 'Original input', 'Original output');
        $this->persistEntity($workflow);

        $this->execute(new Input(
            (string) $workflow->id,
            (string) $example->id,
            input: 'Updated input',
        ));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $persistedWorkflow = $this->findEntity(Workflow::class, ['id' => $workflow->id], true);
        $persistedExample = $persistedWorkflow->examples->first();

        self::assertInstanceOf(Example::class, $persistedExample);
        self::assertSame('Original scenario', $persistedExample->scenario);
        self::assertSame('Updated input', $persistedExample->input);
    }

    public function testItShouldUpdateOutputOnly(): void
    {
        $workflow = self::draftWorkflowEntity();
        $example = Example::create($workflow, 'Original scenario', 'Original input', 'Original output');
        $this->persistEntity($workflow);

        $this->execute(new Input(
            (string) $workflow->id,
            (string) $example->id,
            output: 'Updated output',
        ));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $persistedWorkflow = $this->findEntity(Workflow::class, ['id' => $workflow->id], true);
        $persistedExample = $persistedWorkflow->examples->first();

        self::assertInstanceOf(Example::class, $persistedExample);
        self::assertSame('Original scenario', $persistedExample->scenario);
        self::assertSame('Updated output', $persistedExample->output);
    }

    public function testItShouldUpdateExplanationOnly(): void
    {
        $workflow = self::draftWorkflowEntity();
        $example = Example::create($workflow, 'Original scenario', 'Original input', 'Original output');
        $this->persistEntity($workflow);

        $this->execute(new Input(
            (string) $workflow->id,
            (string) $example->id,
            explanation: 'New explanation',
        ));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $persistedWorkflow = $this->findEntity(Workflow::class, ['id' => $workflow->id], true);
        $persistedExample = $persistedWorkflow->examples->first();

        self::assertInstanceOf(Example::class, $persistedExample);
        self::assertSame('New explanation', $persistedExample->explanation);
    }

    public function testItShouldThrowExceptionWhenWorkflowNotFound(): void
    {
        $this->expectException(WorkflowNotFoundException::class);

        $this->execute(new Input(
            'non-existent-workflow',
            'non-existent-example',
            scenario: 'Updated',
        ));
    }

    public function testItShouldThrowExceptionWhenExampleNotFound(): void
    {
        $workflow = self::draftWorkflowEntity();
        $this->persistEntity($workflow);

        $nonExistentId = '00000000-0000-0000-0000-000000000000';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(\sprintf('Example with ID "%s" not found.', $nonExistentId));

        $this->execute(new Input(
            (string) $workflow->id,
            $nonExistentId,
            scenario: 'Updated',
        ));
    }

    public function testItShouldThrowExceptionWhenNoFieldsProvided(): void
    {
        $workflow = self::draftWorkflowEntity();
        $example = Example::create($workflow, 'Scenario', 'Input', 'Output');
        $this->persistEntity($workflow);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('At least one field must be provided.');

        $this->execute(new Input((string) $workflow->id, (string) $example->id));
    }

    public function testItShouldThrowExceptionWhenWorkflowIsArchived(): void
    {
        $workflow = self::draftWorkflowEntity();
        $example = Example::create($workflow, 'Scenario', 'Input', 'Output');
        $workflow->archive();
        $this->persistEntity($workflow);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot perform this action on an archived directive.');

        $this->execute(new Input(
            (string) $workflow->id,
            (string) $example->id,
            scenario: 'Updated',
        ));
    }
}
