<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\Authoring\Application\Workflow;

use Dairectiv\Authoring\Application\Workflow\Draft\Input;
use Dairectiv\Authoring\Application\Workflow\Draft\Output;
use Dairectiv\Authoring\Domain\Object\Directive\DirectiveState;
use Dairectiv\Authoring\Domain\Object\Directive\Event\DirectiveDrafted;
use Dairectiv\Authoring\Domain\Object\Directive\Exception\DirectiveAlreadyExistsException;
use Dairectiv\Authoring\Domain\Object\Workflow\Workflow;
use Dairectiv\Tests\Framework\IntegrationTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

#[Group('integration')]
#[Group('authoring')]
#[Group('use-case')]
final class DraftTest extends IntegrationTestCase
{
    public function testItShouldDraftWorkflow(): void
    {
        $output = $this->executeDraftWorkflow();

        self::assertDomainEventHasBeenDispatched(DirectiveDrafted::class);

        $workflow = $output->workflow;

        self::assertSame('my-workflow', (string) $workflow->id);
        self::assertSame('My Workflow', $workflow->name);
        self::assertSame('A description of my workflow', $workflow->description);
        self::assertSame(DirectiveState::Draft, $workflow->state);
        self::assertTrue($workflow->examples->isEmpty());
        self::assertTrue($workflow->steps->isEmpty());
        self::assertNull($workflow->content);

        $persistedWorkflow = $this->findEntity(Workflow::class, ['id' => $workflow->id], true);

        self::assertSame('my-workflow', (string) $persistedWorkflow->id);
        self::assertSame('My Workflow', $persistedWorkflow->name);
        self::assertSame('A description of my workflow', $persistedWorkflow->description);
        self::assertSame(DirectiveState::Draft, $persistedWorkflow->state);
        self::assertSame(DirectiveState::Draft, $persistedWorkflow->state);
        self::assertTrue($persistedWorkflow->examples->isEmpty());
        self::assertTrue($persistedWorkflow->steps->isEmpty());
        self::assertNull($persistedWorkflow->content);
    }

    public function testItShouldThrowExceptionWhenWorkflowAlreadyExists(): void
    {
        $workflow = self::draftWorkflow(id: 'my-workflow');
        $this->persistEntity($workflow);

        $this->expectException(DirectiveAlreadyExistsException::class);

        $this->executeDraftWorkflow();
    }

    /**
     * @return iterable<string, array{name: string, expectedId: string}>
     */
    public static function provideNameToIdConversions(): iterable
    {
        yield 'spaces' => ['name' => 'My Awesome Workflow', 'expectedId' => 'my-awesome-workflow'];
        yield 'uppercase' => ['name' => 'UPPERCASE WORKFLOW', 'expectedId' => 'uppercaseworkflow'];
        yield 'mixed case' => ['name' => 'MixedCase Workflow Name', 'expectedId' => 'mixed-case-workflow-name'];
        yield 'already kebab' => ['name' => 'already-kebab-case', 'expectedId' => 'already-kebab-case'];
        yield 'single word' => ['name' => 'Single', 'expectedId' => 'single'];
        yield 'camelCase' => ['name' => 'camelCaseWorkflow', 'expectedId' => 'camel-case-workflow'];
        yield 'PascalCase' => ['name' => 'PascalCaseWorkflow', 'expectedId' => 'pascal-case-workflow'];
        yield 'with numbers' => ['name' => 'Workflow Version 2', 'expectedId' => 'workflow-version2'];
    }

    #[DataProvider('provideNameToIdConversions')]
    public function testItShouldGenerateKebabCaseIdFromName(string $name, string $expectedId): void
    {
        $output = $this->executeDraftWorkflow($name);

        self::assertSame($expectedId, (string) $output->workflow->id);
        self::assertDomainEventHasBeenDispatched(DirectiveDrafted::class);
    }

    public function testItShouldDraftMultipleDistinctWorkflows(): void
    {
        $output1 = $this->executeDraftWorkflow('First Workflow', 'First description');
        $output2 = $this->executeDraftWorkflow('Second Workflow', 'Second description');
        $output3 = $this->executeDraftWorkflow('Third Workflow', 'Third description');

        self::assertSame('first-workflow', (string) $output1->workflow->id);
        self::assertSame('second-workflow', (string) $output2->workflow->id);
        self::assertSame('third-workflow', (string) $output3->workflow->id);

        // Verify all are persisted
        $persistedWorkflow1 = $this->findEntity(Workflow::class, ['id' => $output1->workflow->id], true);
        $persistedWorkflow2 = $this->findEntity(Workflow::class, ['id' => $output2->workflow->id], true);
        $persistedWorkflow3 = $this->findEntity(Workflow::class, ['id' => $output3->workflow->id], true);

        self::assertSame('First Workflow', $persistedWorkflow1->name);
        self::assertSame('Second Workflow', $persistedWorkflow2->name);
        self::assertSame('Third Workflow', $persistedWorkflow3->name);

        self::assertDomainEventHasBeenDispatched(DirectiveDrafted::class, 3);
    }

    public function testItShouldPreserveOriginalNameAndDescription(): void
    {
        $output = $this->executeDraftWorkflow('  Workflow With  Extra   Spaces  ', 'Description with trailing space ');

        // ID should be kebab-cased from trimmed name
        self::assertSame('workflow-with-extra-spaces', (string) $output->workflow->id);

        // But original name and description should be preserved as provided
        self::assertSame('  Workflow With  Extra   Spaces  ', $output->workflow->name);
        self::assertSame('Description with trailing space ', $output->workflow->description);

        self::assertDomainEventHasBeenDispatched(DirectiveDrafted::class);
    }

    private function executeDraftWorkflow(
        string $name = 'My Workflow',
        string $description = 'A description of my workflow',
    ): Output {
        $output = $this->execute(new Input($name, $description));

        self::assertInstanceOf(Output::class, $output);

        return $output;
    }
}
