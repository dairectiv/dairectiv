<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Framework\Helpers;

use Dairectiv\Authoring\Domain\Object\Directive\DirectiveId;
use Dairectiv\Authoring\Domain\Object\Rule\Rule;
use Dairectiv\Authoring\Domain\Object\Skill\Skill;

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

    public static function draftSkill(
        string $id = 'skill-id',
        string $name = 'My Skill',
        string $description = 'Description',
    ): Skill {
        return Skill::draft(self::getDirectiveId($id), $name, $description);
    }
}
