<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\UserInterface\Http\Api\Skill\Draft\Payload;

use Symfony\Component\Validator\Constraints;

final class HybridWorkflowPayload extends WorkflowPayload
{
    /**
     * @param list<StepPayload> $steps
     * @param list<TemplatePayload> $templates
     */
    public function __construct(
        #[Constraints\Valid]
        public array $steps = [],

        #[Constraints\Valid]
        public array $templates = [],
    ) {
    }

    public function toArray(): array
    {
        return [
            'steps' => array_map(
                static fn (StepPayload $stepPayload): array => $stepPayload->toState(),
                $this->steps,
            ),
            'templates' => array_map(
                static fn (TemplatePayload $templatePayload): array => $templatePayload->toState(),
                $this->templates,
            ),
        ];
    }
}
