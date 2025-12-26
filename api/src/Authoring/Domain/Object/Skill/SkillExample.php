<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Domain\Object\Skill;

use Dairectiv\SharedKernel\Domain\Object\Assert;
use Dairectiv\SharedKernel\Domain\Object\ValueObject\ObjectValue;

final readonly class SkillExample implements ObjectValue
{
    private function __construct(
        public string $scenario,
        public string $input,
        public string $output,
        public ?string $explanation,
    ) {
    }

    public static function create(
        string $scenario,
        string $input,
        string $output,
        ?string $explanation = null,
    ): self {
        Assert::notEmpty($scenario, 'Skill example scenario cannot be empty.');
        Assert::notEmpty($input, 'Skill example input cannot be empty.');
        Assert::notEmpty($output, 'Skill example output cannot be empty.');

        return new self($scenario, $input, $output, $explanation);
    }

    public function hasExplanation(): bool
    {
        return null !== $this->explanation;
    }

    public static function fromArray(array $state): static
    {
        Assert::keyExists($state, 'scenario');
        Assert::string($state['scenario']);

        Assert::keyExists($state, 'input');
        Assert::string($state['input']);

        Assert::keyExists($state, 'output');
        Assert::string($state['output']);

        $explanation = $state['explanation'] ?? null;
        Assert::nullOrString($explanation);

        return new self(
            $state['scenario'],
            $state['input'],
            $state['output'],
            $explanation,
        );
    }

    public function toArray(): array
    {
        return [
            'scenario'    => $this->scenario,
            'input'       => $this->input,
            'output'      => $this->output,
            'explanation' => $this->explanation,
        ];
    }
}
