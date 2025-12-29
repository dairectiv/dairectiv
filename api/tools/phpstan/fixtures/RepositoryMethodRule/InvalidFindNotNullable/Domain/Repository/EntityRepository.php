<?php

declare(strict_types=1);

namespace Dairectiv\PHPStan\Fixtures\RepositoryMethodRule\InvalidFindNotNullable\Domain\Repository;

interface EntityRepository
{
    public function findEntityById(string $id): Entity;
}
