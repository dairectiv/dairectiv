<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Fixtures\Domain;

use Dairectiv\SharedKernel\Domain\Object\ValueObject\ObjectValue;

final readonly class FakeObjectValue implements ObjectValue
{
    /**
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public string $name,
        public int $count,
        public ?string $optional = null,
        public array $metadata = [],
    ) {
    }

    public static function fromArray(array $state): static
    {
        /**
         * @var array{name: string, count: int, optional: ?string, metadata: array<string, mixed>} $state
         */
        return new self(
            $state['name'],
            $state['count'],
            $state['optional'],
            $state['metadata'],
        );
    }

    public function toArray(): array
    {
        return [
            'name'     => $this->name,
            'count'    => $this->count,
            'optional' => $this->optional,
            'metadata' => $this->metadata,
        ];
    }
}
