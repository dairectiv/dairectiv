<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\UserInterface\Http\Api\Payload\Workflow\UpdateWorkflowExample;

final readonly class UpdateWorkflowExamplePayload
{
    public function __construct(
        public ?string $scenario = null,
        public ?string $input = null,
        public ?string $output = null,
        public ?string $explanation = null,
    ) {
    }
}
