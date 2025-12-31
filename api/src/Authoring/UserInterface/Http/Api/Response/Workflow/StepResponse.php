<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\UserInterface\Http\Api\Response\Workflow;

use Cake\Chronos\Chronos;
use Dairectiv\Authoring\Domain\Object\Workflow\Step\Step;

final readonly class StepResponse
{
    private function __construct(
        public string $id,
        public Chronos $createdAt,
        public Chronos $updatedAt,
        public int $order,
        public string $content,
    ) {
    }

    public static function fromStep(Step $step): self
    {
        return new self(
            $step->id->toString(),
            $step->createdAt,
            $step->updatedAt,
            $step->order,
            $step->content,
        );
    }
}
