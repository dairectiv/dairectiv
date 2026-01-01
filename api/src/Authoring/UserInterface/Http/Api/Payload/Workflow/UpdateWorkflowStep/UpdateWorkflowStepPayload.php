<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\UserInterface\Http\Api\Payload\Workflow\UpdateWorkflowStep;

final readonly class UpdateWorkflowStepPayload
{
    public function __construct(
        public string $content,
    ) {
    }
}
