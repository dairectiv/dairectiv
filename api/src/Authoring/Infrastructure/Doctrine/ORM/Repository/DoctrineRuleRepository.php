<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Infrastructure\Doctrine\ORM\Repository;

use Dairectiv\Authoring\Domain\Object\Directive\DirectiveId;
use Dairectiv\Authoring\Domain\Object\Rule\Exception\RuleNotFoundException;
use Dairectiv\Authoring\Domain\Object\Rule\Rule;
use Dairectiv\Authoring\Domain\Repository\RuleRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
}
