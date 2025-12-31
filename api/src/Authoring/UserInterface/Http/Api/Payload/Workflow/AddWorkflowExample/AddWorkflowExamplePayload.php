<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\UserInterface\Http\Api\Payload\Workflow\AddWorkflowExample;

use Symfony\Component\Validator\Constraints;

final readonly class AddWorkflowExamplePayload
{
    public function __construct(
        #[Constraints\NotBlank]
        public string $scenario,
        #[Constraints\NotBlank]
        public string $input,
        #[Constraints\NotBlank]
        public string $output,
        public ?string $explanation = null,
    ) {
    }
}
