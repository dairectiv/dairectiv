<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\UserInterface\Http\Api\Response\Workflow;

use Cake\Chronos\Chronos;
use Dairectiv\Authoring\Domain\Object\Workflow\Example\Example;

final readonly class ExampleResponse
{
    private function __construct(
        public string $id,
        public Chronos $createdAt,
        public Chronos $updatedAt,
        public string $scenario,
        public string $input,
        public string $output,
        public ?string $explanation,
    ) {
    }

    public static function fromExample(Example $example): self
    {
        return new self(
            $example->id->toString(),
            $example->createdAt,
            $example->updatedAt,
            $example->scenario,
            $example->input,
            $example->output,
            $example->explanation,
        );
    }
}
