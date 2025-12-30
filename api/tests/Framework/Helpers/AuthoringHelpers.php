<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Framework\Helpers;

use Dairectiv\Authoring\Domain\Object\Directive\DirectiveId;
use Dairectiv\Authoring\Domain\Object\Rule\Rule;
use Dairectiv\Authoring\Domain\Object\Workflow\Workflow;

trait AuthoringHelpers
{
    public static function getDirectiveId(string $directiveId = 'directive-id'): DirectiveId
    {
        return DirectiveId::fromString($directiveId);
    }

    public static function draftRule(
        string $id = 'rule-id',
        string $name = 'My Rule',
        string $description = 'Description',
    ): Rule {
        return Rule::draft(self::getDirectiveId($id), $name, $description);
    }

    public static function draftWorkflow(
        string $id = 'workflow-id',
        string $name = 'My Workflow',
        string $description = 'Description',
    ): Workflow {
        return Workflow::draft(self::getDirectiveId($id), $name, $description);
    }
}
