<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\UserInterface\Http\Api\Payload\Workflow\DraftWorkflow;

use Symfony\Component\Validator\Constraints;

final readonly class DraftWorkflowPayload
{
    public function __construct(
        #[Constraints\NotBlank]
        #[Constraints\Length(max: 255)]
        public string $name,
        #[Constraints\NotBlank]
        #[Constraints\Length(max: 500)]
        public string $description,
    ) {
    }
}
