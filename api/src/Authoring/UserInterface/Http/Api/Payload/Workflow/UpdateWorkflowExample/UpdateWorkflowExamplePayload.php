<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\UserInterface\Http\Api\Payload\Workflow\UpdateWorkflowExample;

final readonly class UpdateWorkflowExamplePayload
{
    public function __construct(
        public string $scenario,
        public string $input,
        public string $output,
        public ?string $explanation = null,
    ) {
    }
}
