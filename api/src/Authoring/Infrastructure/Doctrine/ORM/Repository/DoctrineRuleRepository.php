<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Infrastructure\Doctrine\ORM\Repository;

use Dairectiv\Authoring\Domain\Object\Directive\DirectiveId;
use Dairectiv\Authoring\Domain\Object\Rule\Exception\RuleNotFoundException;
use Dairectiv\Authoring\Domain\Object\Rule\Rule;
use Dairectiv\Authoring\Domain\Object\Rule\RuleSearchCriteria;
use Dairectiv\Authoring\Domain\Repository\RuleRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Rule>
 */
final class DoctrineRuleRepository extends ServiceEntityRepository implements RuleRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Rule::class);
    }

    public function save(Rule $rule): void
    {
        $this->getEntityManager()->persist($rule);
    }

    public function getRuleById(DirectiveId $id): Rule
    {
        $rule = $this->find($id);

        if (null === $rule) {
            throw RuleNotFoundException::fromId($id);
        }

        return $rule;
    }

    public function searchByCriteria(RuleSearchCriteria $criteria, int $offset, int $limit): array
    {
        $qb = $this->createQueryBuilder('r');
        $this->applyCriteria($qb, $criteria);

        $sortField = match ($criteria->sortBy) {
            'name'      => 'r.name',
            'updatedAt' => 'r.updatedAt',
            default     => 'r.createdAt',
        };

        $qb->orderBy($sortField, 'asc' === $criteria->sortOrder ? 'ASC' : 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
        ;

        return $qb->getQuery()->getResult();
    }

    public function countByCriteria(RuleSearchCriteria $criteria): int
    {
        $qb = $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
        ;

        $this->applyCriteria($qb, $criteria);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    private function applyCriteria(QueryBuilder $qb, RuleSearchCriteria $criteria): void
    {
        if (null !== $criteria->search) {
            $qb->andWhere('LOWER(r.name) LIKE LOWER(:search) OR LOWER(r.description) LIKE LOWER(:search)')
                ->setParameter('search', \sprintf('%%%s%%', $criteria->search))
            ;
        }

        if (null !== $criteria->state) {
            $qb->andWhere('r.state = :state')
                ->setParameter('state', $criteria->state)
            ;
        }
    }
}
