<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\UserInterface\Http\Api\Skill\Draft\Payload;

use Symfony\Component\Validator\Constraints;

final class Payload
{
    /**
     * @param list<ExamplePayload> $examples
     */
    public function __construct(
        #[Constraints\NotBlank]
        #[Constraints\Regex('/^[a-z-]+$/')]
        #[Constraints\Length(max: 255)]
        public string $id,

        #[Constraints\NotBlank]
        #[Constraints\Length(max: 255)]
        public string $name,

        #[Constraints\NotBlank]
        #[Constraints\Length(max: 500)]
        public string $description,

        #[Constraints\NotBlank]
        public string $content,

        #[Constraints\Valid]
        public WorkflowPayload $workflow,

        #[Constraints\Valid]
        public array $examples = [],
    ) {
    }
}
