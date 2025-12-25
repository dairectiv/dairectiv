<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Infrastructure\Doctrine\ORM\Repository;

use Dairectiv\Authoring\Domain\Object\Directive\Directive;
use Dairectiv\Authoring\Domain\Object\Directive\DirectiveId;
use Dairectiv\Authoring\Domain\Object\Directive\Exception\DirectiveNotFoundException;
use Dairectiv\Authoring\Domain\Repository\DirectiveRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Directive>
 */
final class DoctrineDirectiveRepository extends ServiceEntityRepository implements DirectiveRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Directive::class);
    }

    public function save(Directive $directive): void
    {
        $this->getEntityManager()->persist($directive);
    }

    public function getDirectiveById(DirectiveId $id): Directive
    {
        $directive = $this->find($id);

        if (null === $directive) {
            throw DirectiveNotFoundException::fromId($id);
        }

        return $directive;
    }

    public function findDirectiveById(DirectiveId $id): ?Directive
    {
        return $this->find($id);
    }
}
