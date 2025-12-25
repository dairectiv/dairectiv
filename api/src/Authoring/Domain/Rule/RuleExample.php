<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Domain\Rule;

use Dairectiv\SharedKernel\Domain\Assert;
use Dairectiv\SharedKernel\Domain\ValueObject\ObjectValue;

final readonly class RuleExample implements ObjectValue
{
    private function __construct(
        public ?string $good,
        public ?string $bad,
        public ?string $explanation,
    ) {
    }

    public static function create(?string $good = null, ?string $bad = null, ?string $explanation = null): self
    {
        Assert::true(
            null !== $good || null !== $bad,
            'Rule example must have at least a good or bad example.',
        );

        return new self($good, $bad, $explanation);
    }

    public static function good(string $code, ?string $explanation = null): self
    {
        return new self($code, null, $explanation);
    }

    public static function bad(string $code, ?string $explanation = null): self
    {
        return new self(null, $code, $explanation);
    }

    public static function transformation(string $bad, string $good, ?string $explanation = null): self
    {
        return new self($good, $bad, $explanation);
    }

    public function hasGood(): bool
    {
        return null !== $this->good;
    }

    public function hasBad(): bool
    {
        return null !== $this->bad;
    }

    public function isTransformation(): bool
    {
        return $this->hasGood() && $this->hasBad();
    }

    public static function fromArray(array $state): static
    {
        Assert::keyExists($state, 'good');
        Assert::nullOrString($state['good']);
        Assert::keyExists($state, 'bad');
        Assert::nullOrString($state['bad']);
        Assert::keyExists($state, 'explanation');
        Assert::nullOrString($state['explanation']);

        return new self($state['good'], $state['bad'], $state['explanation']);
    }

    public function toArray(): array
    {
        return [
            'good'        => $this->good,
            'bad'         => $this->bad,
            'explanation' => $this->explanation,
        ];
    }
}
