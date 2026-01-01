<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\Authoring\Infrastructure\Doctrine\ORM\Repository;

use Dairectiv\Authoring\Domain\Object\Directive\DirectiveId;
use Dairectiv\Authoring\Domain\Object\Directive\DirectiveState;
use Dairectiv\Authoring\Domain\Object\Workflow\Exception\WorkflowNotFoundException;
use Dairectiv\Authoring\Domain\Object\Workflow\Workflow;
use Dairectiv\Authoring\Domain\Object\Workflow\WorkflowSearchCriteria;
use Dairectiv\Authoring\Infrastructure\Doctrine\ORM\Repository\DoctrineWorkflowRepository;
use Dairectiv\Tests\Framework\IntegrationTestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('integration')]
#[Group('authoring')]
#[Group('doctrine-repository')]
final class DoctrineWorkflowRepositoryTest extends IntegrationTestCase
{
    private DoctrineWorkflowRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = self::getService(DoctrineWorkflowRepository::class);
    }

    public function testItShouldSaveAndFindWorkflowById(): void
    {
        $workflow = self::draftWorkflowEntity();

        $this->repository->save($workflow);
        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();

        $found = $this->findEntity(Workflow::class, ['id' => $workflow->id]);

        self::assertNotNull($found);
        self::assertTrue($workflow->id->equals($found->id));
    }

    public function testItShouldSaveAndGetWorkflowById(): void
    {
        $workflow = self::draftWorkflowEntity();

        $this->repository->save($workflow);
        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();

        $found = $this->repository->getWorkflowById($workflow->id);

        self::assertTrue($workflow->id->equals($found->id));
    }

    public function testItShouldThrowExceptionWhenWorkflowNotFound(): void
    {
        $id = DirectiveId::fromString('non-existent-workflow');

        $this->expectException(WorkflowNotFoundException::class);
        $this->expectExceptionMessage('Workflow with ID non-existent-workflow not found.');

        $this->repository->getWorkflowById($id);
    }

    public function testItShouldSearchByCriteriaWithEmptyResult(): void
    {
        $criteria = new WorkflowSearchCriteria();

        $result = $this->repository->searchByCriteria($criteria, 0, 20);

        self::assertSame([], $result);
    }

    public function testItShouldSearchByCriteriaWithPagination(): void
    {
        $this->createWorkflows(15);
        $criteria = new WorkflowSearchCriteria();

        $firstPage = $this->repository->searchByCriteria($criteria, 0, 10);
        $secondPage = $this->repository->searchByCriteria($criteria, 10, 10);

        self::assertCount(10, $firstPage);
        self::assertCount(5, $secondPage);
    }

    public function testItShouldSearchByCriteriaWithSearchOnName(): void
    {
        $this->persistEntity(self::draftWorkflowEntity(id: 'workflow-alpha', name: 'Alpha Workflow'));
        $this->persistEntity(self::draftWorkflowEntity(id: 'workflow-beta', name: 'Beta Workflow'));
        $this->getEntityManager()->clear();

        $criteria = new WorkflowSearchCriteria(search: 'Alpha');

        $result = $this->repository->searchByCriteria($criteria, 0, 20);

        self::assertCount(1, $result);
        self::assertSame('workflow-alpha', (string) $result[0]->id);
    }

    public function testItShouldSearchByCriteriaWithSearchOnDescription(): void
    {
        $this->persistEntity(self::draftWorkflowEntity(id: 'workflow-one', name: 'Workflow One', description: 'Contains keyword'));
        $this->persistEntity(self::draftWorkflowEntity(id: 'workflow-two', name: 'Workflow Two', description: 'Different text'));
        $this->getEntityManager()->clear();

        $criteria = new WorkflowSearchCriteria(search: 'keyword');

        $result = $this->repository->searchByCriteria($criteria, 0, 20);

        self::assertCount(1, $result);
        self::assertSame('workflow-one', (string) $result[0]->id);
    }

    public function testItShouldSearchByCriteriaWithStateFilter(): void
    {
        $draftWorkflow = self::draftWorkflowEntity(id: 'draft-workflow');
        $this->persistEntity($draftWorkflow);

        $publishedWorkflow = self::draftWorkflowEntity(id: 'published-workflow');
        $publishedWorkflow->publish();
        $this->persistEntity($publishedWorkflow);
        $this->getEntityManager()->clear();

        $criteria = new WorkflowSearchCriteria(state: DirectiveState::Published);

        $result = $this->repository->searchByCriteria($criteria, 0, 20);

        self::assertCount(1, $result);
        self::assertSame('published-workflow', (string) $result[0]->id);
    }

    public function testItShouldSearchByCriteriaWithSortByName(): void
    {
        $this->persistEntity(self::draftWorkflowEntity(id: 'workflow-charlie', name: 'Charlie'));
        $this->persistEntity(self::draftWorkflowEntity(id: 'workflow-alpha', name: 'Alpha'));
        $this->persistEntity(self::draftWorkflowEntity(id: 'workflow-bravo', name: 'Bravo'));
        $this->getEntityManager()->clear();

        $criteria = new WorkflowSearchCriteria(sortBy: 'name', sortOrder: 'asc');

        $result = $this->repository->searchByCriteria($criteria, 0, 20);

        self::assertCount(3, $result);
        self::assertSame('workflow-alpha', (string) $result[0]->id);
        self::assertSame('workflow-bravo', (string) $result[1]->id);
        self::assertSame('workflow-charlie', (string) $result[2]->id);
    }

    public function testItShouldSearchByCriteriaWithSortByUpdatedAt(): void
    {
        $this->persistEntity(self::draftWorkflowEntity(id: 'workflow-first'));
        $this->persistEntity(self::draftWorkflowEntity(id: 'workflow-second'));
        $this->getEntityManager()->clear();

        $criteria = new WorkflowSearchCriteria(sortBy: 'updatedAt', sortOrder: 'desc');

        $result = $this->repository->searchByCriteria($criteria, 0, 20);

        self::assertCount(2, $result);
    }

    public function testItShouldSearchByCriteriaWithCombinedFilters(): void
    {
        $draftAlpha = self::draftWorkflowEntity(id: 'draft-alpha', name: 'Alpha Draft');
        $this->persistEntity($draftAlpha);

        $publishedAlpha = self::draftWorkflowEntity(id: 'published-alpha', name: 'Alpha Published');
        $publishedAlpha->publish();
        $this->persistEntity($publishedAlpha);

        $draftBeta = self::draftWorkflowEntity(id: 'draft-beta', name: 'Beta Draft');
        $this->persistEntity($draftBeta);
        $this->getEntityManager()->clear();

        $criteria = new WorkflowSearchCriteria(search: 'Alpha', state: DirectiveState::Draft);

        $result = $this->repository->searchByCriteria($criteria, 0, 20);

        self::assertCount(1, $result);
        self::assertSame('draft-alpha', (string) $result[0]->id);
    }

    public function testItShouldCountByCriteriaWithEmptyResult(): void
    {
        $criteria = new WorkflowSearchCriteria();

        $count = $this->repository->countByCriteria($criteria);

        self::assertSame(0, $count);
    }

    public function testItShouldCountByCriteriaWithAllWorkflows(): void
    {
        $this->createWorkflows(5);
        $criteria = new WorkflowSearchCriteria();

        $count = $this->repository->countByCriteria($criteria);

        self::assertSame(5, $count);
    }

    public function testItShouldCountByCriteriaWithSearchFilter(): void
    {
        $this->persistEntity(self::draftWorkflowEntity(id: 'workflow-alpha', name: 'Alpha Workflow'));
        $this->persistEntity(self::draftWorkflowEntity(id: 'workflow-beta', name: 'Beta Workflow'));
        $this->persistEntity(self::draftWorkflowEntity(id: 'workflow-gamma', name: 'Alpha Gamma'));
        $this->getEntityManager()->clear();

        $criteria = new WorkflowSearchCriteria(search: 'Alpha');

        $count = $this->repository->countByCriteria($criteria);

        self::assertSame(2, $count);
    }

    public function testItShouldCountByCriteriaWithStateFilter(): void
    {
        $draftWorkflow = self::draftWorkflowEntity(id: 'draft-workflow');
        $this->persistEntity($draftWorkflow);

        $publishedWorkflow1 = self::draftWorkflowEntity(id: 'published-workflow-one');
        $publishedWorkflow1->publish();
        $this->persistEntity($publishedWorkflow1);

        $publishedWorkflow2 = self::draftWorkflowEntity(id: 'published-workflow-two');
        $publishedWorkflow2->publish();
        $this->persistEntity($publishedWorkflow2);
        $this->getEntityManager()->clear();

        $criteria = new WorkflowSearchCriteria(state: DirectiveState::Published);

        $count = $this->repository->countByCriteria($criteria);

        self::assertSame(2, $count);
    }

    public function testItShouldCountByCriteriaWithCombinedFilters(): void
    {
        $draftAlpha = self::draftWorkflowEntity(id: 'draft-alpha', name: 'Alpha Draft');
        $this->persistEntity($draftAlpha);

        $publishedAlpha = self::draftWorkflowEntity(id: 'published-alpha', name: 'Alpha Published');
        $publishedAlpha->publish();
        $this->persistEntity($publishedAlpha);

        $draftBeta = self::draftWorkflowEntity(id: 'draft-beta', name: 'Beta Draft');
        $this->persistEntity($draftBeta);
        $this->getEntityManager()->clear();

        $criteria = new WorkflowSearchCriteria(search: 'Alpha', state: DirectiveState::Draft);

        $count = $this->repository->countByCriteria($criteria);

        self::assertSame(1, $count);
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
        $this->getEntityManager()->clear();
    }
}
