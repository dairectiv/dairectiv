<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\Authoring\Application\Workflow;

use Dairectiv\Authoring\Application\Workflow\ListWorkflows\Input;
use Dairectiv\Authoring\Application\Workflow\ListWorkflows\Output;
use Dairectiv\Authoring\Domain\Object\Directive\DirectiveState;
use Dairectiv\Tests\Framework\IntegrationTestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('integration')]
#[Group('authoring')]
#[Group('use-case')]
final class ListWorkflowsTest extends IntegrationTestCase
{
    public function testItShouldReturnEmptyListWhenNoWorkflowsExist(): void
    {
        $output = $this->executeListWorkflows();

        self::assertSame([], $output->items);
        self::assertSame(0, $output->total);
        self::assertSame(1, $output->page);
        self::assertSame(20, $output->limit);
        self::assertSame(0, $output->totalPages());
        self::assertFalse($output->hasNextPage());
        self::assertFalse($output->hasPreviousPage());
    }

    public function testItShouldReturnAllWorkflowsWithDefaultPagination(): void
    {
        $this->createWorkflows(5);

        $output = $this->executeListWorkflows();

        self::assertCount(5, $output->items);
        self::assertSame(5, $output->total);
        self::assertSame(1, $output->page);
        self::assertSame(20, $output->limit);
        self::assertSame(1, $output->totalPages());
        self::assertFalse($output->hasNextPage());
        self::assertFalse($output->hasPreviousPage());
    }

    public function testItShouldPaginateResults(): void
    {
        $this->createWorkflows(25);

        $output = $this->executeListWorkflows(page: 1, limit: 10);

        self::assertCount(10, $output->items);
        self::assertSame(25, $output->total);
        self::assertSame(1, $output->page);
        self::assertSame(10, $output->limit);
        self::assertSame(3, $output->totalPages());
        self::assertTrue($output->hasNextPage());
        self::assertFalse($output->hasPreviousPage());

        $output = $this->executeListWorkflows(page: 2, limit: 10);

        self::assertCount(10, $output->items);
        self::assertSame(2, $output->page);
        self::assertTrue($output->hasNextPage());
        self::assertTrue($output->hasPreviousPage());

        $output = $this->executeListWorkflows(page: 3, limit: 10);

        self::assertCount(5, $output->items);
        self::assertSame(3, $output->page);
        self::assertFalse($output->hasNextPage());
        self::assertTrue($output->hasPreviousPage());
    }

    public function testItShouldSearchByName(): void
    {
        $this->persistEntity(self::draftWorkflowEntity(id: 'workflow-alpha', name: 'Alpha Workflow'));
        $this->persistEntity(self::draftWorkflowEntity(id: 'workflow-beta', name: 'Beta Workflow'));
        $this->persistEntity(self::draftWorkflowEntity(id: 'workflow-gamma', name: 'Gamma Workflow'));

        $output = $this->executeListWorkflows(search: 'Alpha');

        self::assertCount(1, $output->items);
        self::assertSame('workflow-alpha', (string) $output->items[0]->id);
    }

    public function testItShouldSearchByDescription(): void
    {
        $this->persistEntity(self::draftWorkflowEntity(id: 'workflow-one', name: 'Workflow One', description: 'Description with keyword'));
        $this->persistEntity(self::draftWorkflowEntity(id: 'workflow-two', name: 'Workflow Two', description: 'Another description'));

        $output = $this->executeListWorkflows(search: 'keyword');

        self::assertCount(1, $output->items);
        self::assertSame('workflow-one', (string) $output->items[0]->id);
    }

    public function testItShouldSearchCaseInsensitive(): void
    {
        $this->persistEntity(self::draftWorkflowEntity(id: 'workflow-upper', name: 'UPPERCASE Workflow'));
        $this->persistEntity(self::draftWorkflowEntity(id: 'workflow-lower', name: 'lowercase workflow'));

        $output = $this->executeListWorkflows(search: 'uppercase');

        self::assertCount(1, $output->items);
        self::assertSame('workflow-upper', (string) $output->items[0]->id);
    }

    public function testItShouldFilterByDraftState(): void
    {
        $draftWorkflow = self::draftWorkflowEntity(id: 'draft-workflow');
        $this->persistEntity($draftWorkflow);

        $publishedWorkflow = self::draftWorkflowEntity(id: 'published-workflow');
        $publishedWorkflow->publish();
        $this->persistEntity($publishedWorkflow);

        $output = $this->executeListWorkflows(state: 'draft');

        self::assertCount(1, $output->items);
        self::assertSame('draft-workflow', (string) $output->items[0]->id);
    }

    public function testItShouldFilterByPublishedState(): void
    {
        $draftWorkflow = self::draftWorkflowEntity(id: 'draft-workflow');
        $this->persistEntity($draftWorkflow);

        $publishedWorkflow = self::draftWorkflowEntity(id: 'published-workflow');
        $publishedWorkflow->publish();
        $this->persistEntity($publishedWorkflow);

        $output = $this->executeListWorkflows(state: 'published');

        self::assertCount(1, $output->items);
        self::assertSame('published-workflow', (string) $output->items[0]->id);
    }

    public function testItShouldFilterByArchivedState(): void
    {
        $draftWorkflow = self::draftWorkflowEntity(id: 'draft-workflow');
        $this->persistEntity($draftWorkflow);

        $archivedWorkflow = self::draftWorkflowEntity(id: 'archived-workflow');
        $archivedWorkflow->publish();
        $archivedWorkflow->archive();
        $this->persistEntity($archivedWorkflow);

        $output = $this->executeListWorkflows(state: 'archived');

        self::assertCount(1, $output->items);
        // archive() appends a UUID suffix to the ID
        self::assertStringStartsWith('archived-workflow-', (string) $output->items[0]->id);
    }

    public function testItShouldSortByNameAscending(): void
    {
        $this->persistEntity(self::draftWorkflowEntity(id: 'workflow-charlie', name: 'Charlie'));
        $this->persistEntity(self::draftWorkflowEntity(id: 'workflow-alpha', name: 'Alpha'));
        $this->persistEntity(self::draftWorkflowEntity(id: 'workflow-bravo', name: 'Bravo'));

        $output = $this->executeListWorkflows(sortBy: 'name', sortOrder: 'asc');

        self::assertCount(3, $output->items);
        self::assertSame('workflow-alpha', (string) $output->items[0]->id);
        self::assertSame('workflow-bravo', (string) $output->items[1]->id);
        self::assertSame('workflow-charlie', (string) $output->items[2]->id);
    }

    public function testItShouldSortByNameDescending(): void
    {
        $this->persistEntity(self::draftWorkflowEntity(id: 'workflow-alpha', name: 'Alpha'));
        $this->persistEntity(self::draftWorkflowEntity(id: 'workflow-charlie', name: 'Charlie'));
        $this->persistEntity(self::draftWorkflowEntity(id: 'workflow-bravo', name: 'Bravo'));

        $output = $this->executeListWorkflows(sortBy: 'name', sortOrder: 'desc');

        self::assertCount(3, $output->items);
        self::assertSame('workflow-charlie', (string) $output->items[0]->id);
        self::assertSame('workflow-bravo', (string) $output->items[1]->id);
        self::assertSame('workflow-alpha', (string) $output->items[2]->id);
    }

    public function testItShouldSortByCreatedAt(): void
    {
        $this->persistEntity(self::draftWorkflowEntity(id: 'workflow-first', name: 'First'));
        $this->persistEntity(self::draftWorkflowEntity(id: 'workflow-second', name: 'Second'));
        $this->persistEntity(self::draftWorkflowEntity(id: 'workflow-third', name: 'Third'));

        $output = $this->executeListWorkflows(sortBy: 'createdAt', sortOrder: 'asc');

        self::assertCount(3, $output->items);
        self::assertSame('workflow-first', (string) $output->items[0]->id);
        self::assertSame('workflow-second', (string) $output->items[1]->id);
        self::assertSame('workflow-third', (string) $output->items[2]->id);
    }

    public function testItShouldCombineSearchAndStateFilter(): void
    {
        $draftAlpha = self::draftWorkflowEntity(id: 'draft-alpha', name: 'Alpha Draft');
        $this->persistEntity($draftAlpha);

        $publishedAlpha = self::draftWorkflowEntity(id: 'published-alpha', name: 'Alpha Published');
        $publishedAlpha->publish();
        $this->persistEntity($publishedAlpha);

        $draftBeta = self::draftWorkflowEntity(id: 'draft-beta', name: 'Beta Draft');
        $this->persistEntity($draftBeta);

        $output = $this->executeListWorkflows(search: 'Alpha', state: 'draft');

        self::assertCount(1, $output->items);
        self::assertSame('draft-alpha', (string) $output->items[0]->id);
    }

    public function testItShouldExcludeDeletedWorkflows(): void
    {
        $activeWorkflow = self::draftWorkflowEntity(id: 'active-workflow');
        $this->persistEntity($activeWorkflow);

        $deletedWorkflow = self::draftWorkflowEntity(id: 'deleted-workflow');
        $deletedWorkflow->delete();
        $this->persistEntity($deletedWorkflow);

        $output = $this->executeListWorkflows();

        self::assertCount(1, $output->items);
        self::assertSame('active-workflow', (string) $output->items[0]->id);
    }

    public function testItShouldIgnoreInvalidStateFilter(): void
    {
        $this->persistEntity(self::draftWorkflowEntity(id: 'workflow-one'));
        $this->persistEntity(self::draftWorkflowEntity(id: 'workflow-two'));

        $output = $this->executeListWorkflows(state: 'invalid-state');

        self::assertCount(2, $output->items);
    }

    public function testItShouldReturnWorkflowsWithAllProperties(): void
    {
        $workflow = self::draftWorkflowEntity(id: 'full-workflow', name: 'Full Workflow', description: 'Full description');
        $workflow->updateContent('Full content');
        $this->persistEntity($workflow);

        $output = $this->executeListWorkflows();

        self::assertCount(1, $output->items);
        $returnedWorkflow = $output->items[0];

        self::assertSame('full-workflow', (string) $returnedWorkflow->id);
        self::assertSame('Full Workflow', $returnedWorkflow->name);
        self::assertSame('Full description', $returnedWorkflow->description);
        self::assertSame('Full content', $returnedWorkflow->content);
        self::assertSame(DirectiveState::Draft, $returnedWorkflow->state);
    }

    private const array NAMES = [
        'alpha', 'bravo', 'charlie', 'delta', 'echo', 'foxtrot', 'golf', 'hotel',
        'india', 'juliet', 'kilo', 'lima', 'mike', 'november', 'oscar', 'papa',
        'quebec', 'romeo', 'sierra', 'tango', 'uniform', 'victor', 'whiskey', 'xray',
        'yankee', 'zulu',
    ];

    /**
     * @param int<1, max> $count
     */
    private function createWorkflows(int $count): void
    {
        for ($i = 0; $i < $count; ++$i) {
            $name = self::NAMES[$i % 26];
            $suffix = $i >= 26 ? \sprintf('-%d', (int) ($i / 26)) : '';
            $this->persistEntity(self::draftWorkflowEntity(
                id: \sprintf('workflow-%s%s', $name, $suffix),
                name: \sprintf('Workflow %s%s', ucfirst($name), $suffix),
            ));
        }
    }

    private function executeListWorkflows(
        int $page = 1,
        int $limit = 20,
        ?string $search = null,
        ?string $state = null,
        string $sortBy = 'createdAt',
        string $sortOrder = 'desc',
    ): Output {
        $output = $this->fetch(new Input(
            page: $page,
            limit: $limit,
            search: $search,
            state: $state,
            sortBy: $sortBy,
            sortOrder: $sortOrder,
        ));

        self::assertInstanceOf(Output::class, $output);

        return $output;
    }
}
