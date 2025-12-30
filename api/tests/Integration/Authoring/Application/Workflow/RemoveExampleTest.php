<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\Authoring\Application\Workflow;

use Dairectiv\Authoring\Application\Workflow\RemoveExample\Input;
use Dairectiv\Authoring\Domain\Object\Directive\Event\DirectiveUpdated;
use Dairectiv\Authoring\Domain\Object\Directive\Exception\DirectiveNotFoundException;
use Dairectiv\Authoring\Domain\Object\Workflow\Example\Example;
use Dairectiv\Authoring\Domain\Object\Workflow\Workflow;
use Dairectiv\SharedKernel\Domain\Object\Exception\InvalidArgumentException;
use Dairectiv\Tests\Framework\IntegrationTestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('integration')]
#[Group('authoring')]
#[Group('use-case')]
final class RemoveExampleTest extends IntegrationTestCase
{
    public function testItShouldRemoveExampleFromWorkflow(): void
    {
        $workflow = self::draftWorkflow();
        $example = Example::create($workflow, 'Scenario', 'Input', 'Output', 'Explanation');
        $this->persistEntity($workflow);

        self::assertCount(1, $workflow->examples);

        $this->execute(new Input((string) $workflow->id, (string) $example->id));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $persistedWorkflow = $this->findEntity(Workflow::class, ['id' => $workflow->id], true);

        self::assertCount(0, $persistedWorkflow->examples);
    }

    public function testItShouldRemoveOneExampleFromMultiple(): void
    {
        $workflow = self::draftWorkflow();
        Example::create($workflow, 'Scenario 1', 'Input 1', 'Output 1');
        $example2 = Example::create($workflow, 'Scenario 2', 'Input 2', 'Output 2');
        Example::create($workflow, 'Scenario 3', 'Input 3', 'Output 3');
        $this->persistEntity($workflow);

        self::assertCount(3, $workflow->examples);

        $this->execute(new Input((string) $workflow->id, (string) $example2->id));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $persistedWorkflow = $this->findEntity(Workflow::class, ['id' => $workflow->id], true);

        self::assertCount(2, $persistedWorkflow->examples);

        $remainingScenarios = $persistedWorkflow->examples->map(static fn ($e) => $e->scenario)->toArray();
        self::assertContains('Scenario 1', $remainingScenarios);
        self::assertContains('Scenario 3', $remainingScenarios);
        self::assertNotContains('Scenario 2', $remainingScenarios);
    }

    public function testItShouldThrowExceptionWhenWorkflowNotFound(): void
    {
        $this->expectException(DirectiveNotFoundException::class);

        $this->execute(new Input('non-existent-workflow', '00000000-0000-0000-0000-000000000000'));
    }

    public function testItShouldThrowExceptionWhenExampleNotFound(): void
    {
        $workflow = self::draftWorkflow();
        $this->persistEntity($workflow);

        $nonExistentId = '00000000-0000-0000-0000-000000000000';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(\sprintf('Example with ID "%s" not found.', $nonExistentId));

        $this->execute(new Input((string) $workflow->id, $nonExistentId));
    }

    public function testItShouldThrowExceptionWhenWorkflowIsArchived(): void
    {
        $workflow = self::draftWorkflow();
        $example = Example::create($workflow, 'Scenario', 'Input', 'Output');
        $workflow->archive();
        $this->persistEntity($workflow);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot perform this action on an archived directive.');

        $this->execute(new Input((string) $workflow->id, (string) $example->id));
    }
}
