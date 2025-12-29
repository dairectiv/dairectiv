<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\UserInterface\Http\Api\Rule\Draft;

use Dairectiv\Authoring\Domain\Object\Rule\Rule;

/**
 * @phpstan-type TExample=array{good?: ?string, bad?: ?string, explanation?: ?string}
 */
final readonly class Response
{
    /**
     * @param list<TExample> $examples
     */
    public function __construct(
        public string $id,
        public string $state,
        public string $createdAt,
        public string $updatedAt,
        public string $name,
        public string $description,
        public string $content,
        public array $examples,
    ) {
    }

    public static function fromRule(Rule $rule): self
    {
        /** @var array{examples: list<TExample>} $examples */
        $examples = $rule->examples->toArray();

        return new self(
            (string) $rule->id,
            $rule->state->value,
            $rule->createdAt->toIso8601String(),
            $rule->updatedAt->toIso8601String(),
            (string) $rule->metadata->name,
            (string) $rule->metadata->description,
            (string) $rule->content,
            $examples['examples'],
        );
    }
}
