<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\UserInterface\Http\Api\Response\Rule;

use Cake\Chronos\Chronos;
use Dairectiv\Authoring\Domain\Object\Rule\Example\Example;

final readonly class ExampleResponse
{
    private function __construct(
        public string $id,
        public Chronos $createdAt,
        public Chronos $updatedAt,
        public ?string $good,
        public ?string $bad,
        public ?string $explanation,
    ) {
    }

    public static function fromExample(Example $example): self
    {
        return new self(
            $example->id->toString(),
            $example->createdAt,
            $example->updatedAt,
            $example->good,
            $example->bad,
            $example->explanation,
        );
    }
}
