<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\UserInterface\Http\Api\Payload\Workflow\MoveWorkflowStep;

use Symfony\Component\Validator\Constraints;

final readonly class MoveWorkflowStepPayload
{
    public function __construct(
        #[Constraints\NotBlank]
        #[Constraints\Positive]
        public int $position,
    ) {
    }
}
