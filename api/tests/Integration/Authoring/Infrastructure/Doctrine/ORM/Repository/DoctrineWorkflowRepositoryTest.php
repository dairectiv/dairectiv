<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\Authoring\Infrastructure\Doctrine\ORM\Repository;

use Dairectiv\Authoring\Domain\Object\Directive\DirectiveId;
use Dairectiv\Authoring\Domain\Object\Workflow\Exception\WorkflowNotFoundException;
use Dairectiv\Authoring\Domain\Object\Workflow\Workflow;
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
}
