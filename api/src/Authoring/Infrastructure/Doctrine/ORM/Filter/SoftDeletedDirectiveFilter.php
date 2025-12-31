<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Infrastructure\Doctrine\ORM\Filter;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

final class SoftDeletedDirectiveFilter extends SQLFilter
{
    public function addFilterConstraint(ClassMetadata $targetEntity, string $targetTableAlias): string
    {
        if ('authoring_directive' !== $targetEntity->getTableName()) {
            return '';
        }

        return \sprintf("%s.state != 'deleted'", $targetTableAlias);
    }
}
