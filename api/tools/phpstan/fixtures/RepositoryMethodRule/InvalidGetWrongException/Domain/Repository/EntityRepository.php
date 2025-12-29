<?php

declare(strict_types=1);

namespace Dairectiv\PHPStan\Fixtures\RepositoryMethodRule\InvalidGetWrongException\Domain\Repository;

interface EntityRepository
{
    /**
     * @throws WrongException
     */
    public function getEntityById(string $id): Entity;
}
