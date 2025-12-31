<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\UserInterface\Http\Api\Payload\Workflow\AddWorkflowStep;

use Symfony\Component\Validator\Constraints;

final readonly class AddWorkflowStepPayload
{
    public function __construct(
        #[Constraints\NotBlank]
        public string $content,
        public ?string $afterStepId = null,
    ) {
    }
}
