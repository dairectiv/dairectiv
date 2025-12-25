<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Domain\Rule;

use Dairectiv\Authoring\Domain\ChangeSet\Change;
use Dairectiv\Authoring\Domain\Directive\Directive;

/**
 * @extends Directive<RuleChange>
 */
final class Rule extends Directive
{
    protected function doApplyChanges(Change $change): void
    {
    }
}
