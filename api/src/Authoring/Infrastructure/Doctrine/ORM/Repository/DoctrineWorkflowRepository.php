<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Infrastructure\Doctrine\ORM\Repository;

use Dairectiv\Authoring\Domain\Object\Directive\DirectiveId;
use Dairectiv\Authoring\Domain\Object\Workflow\Exception\WorkflowNotFoundException;
use Dairectiv\Authoring\Domain\Object\Workflow\Workflow;
use Dairectiv\Authoring\Domain\Object\Workflow\WorkflowSearchCriteria;
use Dairectiv\Authoring\Domain\Repository\WorkflowRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
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

    public function searchByCriteria(WorkflowSearchCriteria $criteria, int $offset, int $limit): array
    {
        $qb = $this->createQueryBuilder('w');
        $this->applyCriteria($qb, $criteria);

        $sortField = match ($criteria->sortBy) {
            'name'      => 'w.name',
            'updatedAt' => 'w.updatedAt',
            default     => 'w.createdAt',
        };

        $qb->orderBy($sortField, 'asc' === $criteria->sortOrder ? 'ASC' : 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
        ;

        return $qb->getQuery()->getResult();
    }

    public function countByCriteria(WorkflowSearchCriteria $criteria): int
    {
        $qb = $this->createQueryBuilder('w')
            ->select('COUNT(w.id)')
        ;

        $this->applyCriteria($qb, $criteria);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    private function applyCriteria(QueryBuilder $qb, WorkflowSearchCriteria $criteria): void
    {
        if (null !== $criteria->search) {
            $qb->andWhere('LOWER(w.name) LIKE LOWER(:search) OR LOWER(w.description) LIKE LOWER(:search)')
                ->setParameter('search', \sprintf('%%%s%%', $criteria->search))
            ;
        }

        if (null !== $criteria->state) {
            $qb->andWhere('w.state = :state')
                ->setParameter('state', $criteria->state)
            ;
        }
    }
}
