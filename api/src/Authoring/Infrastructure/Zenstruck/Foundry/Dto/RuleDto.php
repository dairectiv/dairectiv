<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Infrastructure\Zenstruck\Foundry\Dto;

use Cake\Chronos\Chronos;
use Dairectiv\Authoring\Domain\Object\Directive\DirectiveId;
use Dairectiv\Authoring\Domain\Object\Directive\Metadata\DirectiveDescription;
use Dairectiv\Authoring\Domain\Object\Directive\Metadata\DirectiveMetadata;
use Dairectiv\Authoring\Domain\Object\Directive\Metadata\DirectiveName;
use Dairectiv\Authoring\Domain\Object\Rule\Rule;
use Dairectiv\Authoring\Domain\Object\Rule\RuleContent;
use Dairectiv\Authoring\Domain\Object\Rule\RuleExample;
use Dairectiv\Authoring\Domain\Object\Rule\RuleExamples;

final class RuleDto
{
    public string $id;

    public string $name;

    public string $description;

    public string $content;

    public Chronos $createdAt;

    public Chronos $updatedAt;

    /**
     * @var list<RuleExample>
     */
    public array $examples;

    public function build(): Rule
    {
        $now = Chronos::now();
        Chronos::setTestNow($this->createdAt);

        $rule = Rule::draft(
            DirectiveId::fromString($this->id),
            DirectiveMetadata::create(
                DirectiveName::fromString($this->name),
                DirectiveDescription::fromString($this->description),
            ),
            RuleContent::fromString($this->content),
            RuleExamples::fromList($this->examples),
        );

        Chronos::setTestNow($now);

        return $rule;
    }
}
