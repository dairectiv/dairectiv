<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\UserInterface\Http\Api\Skill\Draft\Payload;

use Symfony\Component\Validator\Constraints;

final class SequentialWorkflowPayload extends WorkflowPayload
{
    /**
     * @param list<StepPayload> $steps
     */
    public function __construct(
        #[Constraints\Valid]
        public array $steps = [],
    ) {
    }

    public function toArray(): array
    {
        return [
            'steps' => array_map(
                static fn (StepPayload $stepPayload): array => $stepPayload->toState(),
                $this->steps,
            ),
        ];
    }
}
