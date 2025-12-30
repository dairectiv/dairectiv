<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Infrastructure\Doctrine\ORM\Repository;

use Dairectiv\Authoring\Domain\Object\Directive\DirectiveId;
use Dairectiv\Authoring\Domain\Object\Workflow\Exception\WorkflowNotFoundException;
use Dairectiv\Authoring\Domain\Object\Workflow\Workflow;
use Dairectiv\Authoring\Domain\Repository\WorkflowRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Workflow>
 */
final class DoctrineWorkflowRepository extends ServiceEntityRepository implements WorkflowRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Workflow::class);
    }

    public function save(Workflow $workflow): void
    {
        $this->getEntityManager()->persist($workflow);
    }

    public function getWorkflowById(DirectiveId $id): Workflow
    {
        $workflow = $this->find($id);

        if (null === $workflow) {
            throw WorkflowNotFoundException::fromId($id);
        }

        return $workflow;
    }
}
