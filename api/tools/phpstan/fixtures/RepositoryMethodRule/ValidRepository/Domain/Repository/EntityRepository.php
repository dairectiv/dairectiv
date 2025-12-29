<?php

declare(strict_types=1);

namespace Dairectiv\PHPStan\Fixtures\RepositoryMethodRule\ValidRepository\Domain\Repository;

interface EntityRepository
{
    /**
     * @throws EntityNotFoundException
     */
    public function getEntityById(string $id): Entity;

    public function findEntityById(string $id): ?Entity;
}
