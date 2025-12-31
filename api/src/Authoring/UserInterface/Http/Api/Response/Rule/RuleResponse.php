<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\UserInterface\Http\Api\Response\Rule;

use Cake\Chronos\Chronos;
use Dairectiv\Authoring\Domain\Object\Directive\DirectiveState;
use Dairectiv\Authoring\Domain\Object\Rule\Rule;

final readonly class RuleResponse
{
    /**
     * @param list<ExampleResponse> $examples
     */
    private function __construct(
        public string $id,
        public Chronos $createdAt,
        public Chronos $updatedAt,
        public DirectiveState $state,
        public string $name,
        public string $description,
        public ?string $content,
        public array $examples,
    ) {
    }

    public static function fromRule(Rule $rule): self
    {
        return new self(
            (string) $rule->id,
            $rule->createdAt,
            $rule->updatedAt,
            $rule->state,
            $rule->name,
            $rule->description,
            $rule->content,
            array_values($rule->examples->map(ExampleResponse::fromExample(...))->toArray()),
        );
    }
}
