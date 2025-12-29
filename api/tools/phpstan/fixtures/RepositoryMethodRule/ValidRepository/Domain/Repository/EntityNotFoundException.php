<?php

declare(strict_types=1);

namespace Dairectiv\PHPStan\Fixtures\RepositoryMethodRule\ValidRepository\Domain\Repository;

use Dairectiv\SharedKernel\Domain\Object\Exception\EntityNotFoundException as BaseEntityNotFoundException;

final class EntityNotFoundException extends BaseEntityNotFoundException
{
}
