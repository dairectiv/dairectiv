<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Domain\Repository;

use Dairectiv\Authoring\Domain\Object\Directive\DirectiveId;
use Dairectiv\Authoring\Domain\Object\Rule\Exception\RuleNotFoundException;
use Dairectiv\Authoring\Domain\Object\Rule\Rule;
use Dairectiv\Authoring\Domain\Object\Rule\RuleSearchCriteria;

interface RuleRepository
{
    public function save(Rule $rule): void;

    /**
     * @throws RuleNotFoundException
     */
    public function getRuleById(DirectiveId $id): Rule;

    /**
     * @return list<Rule>
     */
    public function searchByCriteria(RuleSearchCriteria $criteria, int $offset, int $limit): array;

    public function countByCriteria(RuleSearchCriteria $criteria): int;
}
