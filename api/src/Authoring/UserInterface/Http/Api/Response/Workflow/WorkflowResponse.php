<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\UserInterface\Http\Api\Response\Workflow;

use Cake\Chronos\Chronos;
use Dairectiv\Authoring\Domain\Object\Directive\DirectiveState;
use Dairectiv\Authoring\Domain\Object\Workflow\Workflow;

final readonly class WorkflowResponse
{
    /**
     * @param list<ExampleResponse> $examples
     * @param list<StepResponse> $steps
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
        public array $steps,
    ) {
    }

    public static function fromWorkflow(Workflow $workflow): self
    {
        $steps = $workflow->steps->toArray();
        usort($steps, static fn ($a, $b) => $a->order <=> $b->order);

        return new self(
            (string) $workflow->id,
            $workflow->createdAt,
            $workflow->updatedAt,
            $workflow->state,
            $workflow->name,
            $workflow->description,
            $workflow->content,
            array_values($workflow->examples->map(ExampleResponse::fromExample(...))->toArray()),
            array_map(StepResponse::fromStep(...), $steps),
        );
    }
}
