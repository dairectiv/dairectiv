<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\UserInterface\Http\Api\Skill\Draft\Payload;

use Symfony\Component\Validator\Constraints;

final class ChecklistWorkflowPayload extends WorkflowPayload
{
    /**
     * @param list<StepPayload> $items
     */
    public function __construct(
        #[Constraints\Valid]
        public array $items = [],
    ) {
    }

    public function toArray(): array
    {
        return [
            'items' => array_map(
                static fn (StepPayload $stepPayload): array => $stepPayload->toState(),
                $this->items,
            ),
        ];
    }
}
