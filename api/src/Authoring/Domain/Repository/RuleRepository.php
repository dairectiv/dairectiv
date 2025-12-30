<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Domain\Repository;

use Dairectiv\Authoring\Domain\Object\Directive\DirectiveId;
use Dairectiv\Authoring\Domain\Object\Rule\Exception\RuleNotFoundException;
use Dairectiv\Authoring\Domain\Object\Rule\Rule;

interface RuleRepository
{
    public function save(Rule $rule): void;

    /**
     * @throws RuleNotFoundException
     */
    public function getRuleById(DirectiveId $id): Rule;
}
