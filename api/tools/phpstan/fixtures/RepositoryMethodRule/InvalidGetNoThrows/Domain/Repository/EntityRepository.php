<?php

declare(strict_types=1);

namespace Dairectiv\PHPStan\Fixtures\RepositoryMethodRule\InvalidGetNoThrows\Domain\Repository;

interface EntityRepository
{
    public function getEntityById(string $id): Entity;
}
