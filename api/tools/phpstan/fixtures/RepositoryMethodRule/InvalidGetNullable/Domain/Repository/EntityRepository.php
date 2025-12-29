<?php

declare(strict_types=1);

namespace Dairectiv\PHPStan\Fixtures\RepositoryMethodRule\InvalidGetNullable\Domain\Repository;

interface EntityRepository
{
    /**
     * @throws EntityNotFoundException
     */
    public function getEntityById(string $id): ?Entity;
}
