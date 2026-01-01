<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\Authoring\Application\Workflow;

use Dairectiv\Authoring\Application\Workflow\UpdateWorkflow\Input;
use Dairectiv\Authoring\Domain\Object\Directive\DirectiveState;
use Dairectiv\Authoring\Domain\Object\Directive\Event\DirectiveUpdated;
use Dairectiv\Authoring\Domain\Object\Workflow\Exception\WorkflowNotFoundException;
use Dairectiv\Authoring\Domain\Object\Workflow\Workflow;
use Dairectiv\SharedKernel\Domain\Object\Exception\InvalidArgumentException;
use Dairectiv\Tests\Framework\IntegrationTestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('integration')]
#[Group('authoring')]
#[Group('use-case')]
final class UpdateWorkflowTest extends IntegrationTestCase
{
    public function testItShouldUpdateWorkflowMetadata(): void
    {
        $workflow = self::draftWorkflowEntity();
        $this->persistEntity($workflow);

        $this->execute(new Input(
            (string) $workflow->id,
            name: 'Updated Name',
            description: 'Updated Description',
        ));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $persistedWorkflow = $this->findEntity(Workflow::class, ['id' => $workflow->id], true);

        self::assertSame('Updated Name', $persistedWorkflow->name);
        self::assertSame('Updated Description', $persistedWorkflow->description);
        self::assertSame(DirectiveState::Draft, $persistedWorkflow->state);
    }

    public function testItShouldUpdateWorkflowNameOnly(): void
    {
        $workflow = self::draftWorkflowEntity(name: 'Original Name', description: 'Original Description');
        $this->persistEntity($workflow);

        $this->execute(new Input(
            (string) $workflow->id,
            name: 'New Name',
        ));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $persistedWorkflow = $this->findEntity(Workflow::class, ['id' => $workflow->id], true);

        self::assertSame('New Name', $persistedWorkflow->name);
        self::assertSame('Original Description', $persistedWorkflow->description);
    }

    public function testItShouldUpdateWorkflowDescriptionOnly(): void
    {
        $workflow = self::draftWorkflowEntity(name: 'Original Name', description: 'Original Description');
        $this->persistEntity($workflow);

        $this->execute(new Input(
            (string) $workflow->id,
            description: 'New Description',
        ));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $persistedWorkflow = $this->findEntity(Workflow::class, ['id' => $workflow->id], true);

        self::assertSame('Original Name', $persistedWorkflow->name);
        self::assertSame('New Description', $persistedWorkflow->description);
    }

    public function testItShouldUpdateWorkflowContentOnly(): void
    {
        $workflow = self::draftWorkflowEntity();
        $this->persistEntity($workflow);

        $this->execute(new Input(
            (string) $workflow->id,
            content: 'New workflow content',
        ));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $persistedWorkflow = $this->findEntity(Workflow::class, ['id' => $workflow->id], true);

        self::assertSame('New workflow content', $persistedWorkflow->content);
    }

    public function testItShouldUpdateAllFields(): void
    {
        $workflow = self::draftWorkflowEntity(name: 'Original Name', description: 'Original Description');
        $this->persistEntity($workflow);

        $this->execute(new Input(
            (string) $workflow->id,
            name: 'New Name',
            description: 'New Description',
            content: 'New Content',
        ));

        // 2 events: one for metadata, one for content
        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class, 2);

        $persistedWorkflow = $this->findEntity(Workflow::class, ['id' => $workflow->id], true);

        self::assertSame('New Name', $persistedWorkflow->name);
        self::assertSame('New Description', $persistedWorkflow->description);
        self::assertSame('New Content', $persistedWorkflow->content);
    }

    public function testItShouldThrowExceptionWhenWorkflowNotFound(): void
    {
        $this->expectException(WorkflowNotFoundException::class);

        $this->execute(new Input(
            'non-existent-workflow',
            name: 'New Name',
        ));
    }

    public function testItShouldThrowExceptionWhenWorkflowIsArchived(): void
    {
        $workflow = self::draftWorkflowEntity();
        $workflow->archive();
        $this->persistEntity($workflow);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot perform this action on an archived directive.');

        $this->execute(new Input(
            (string) $workflow->id,
            name: 'New Name',
        ));
    }

    public function testItShouldThrowExceptionWhenNoFieldsProvided(): void
    {
        $workflow = self::draftWorkflowEntity();
        $this->persistEntity($workflow);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('At least one field must be provided.');

        $this->execute(new Input((string) $workflow->id));
    }

    public function testItShouldUpdatePublishedWorkflow(): void
    {
        $workflow = self::draftWorkflowEntity();
        $workflow->publish();
        $this->persistEntity($workflow);

        $this->execute(new Input(
            (string) $workflow->id,
            name: 'Updated Published Workflow',
        ));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);

        $persistedWorkflow = $this->findEntity(Workflow::class, ['id' => $workflow->id], true);

        self::assertSame('Updated Published Workflow', $persistedWorkflow->name);
        self::assertSame(DirectiveState::Published, $persistedWorkflow->state);
    }
}
