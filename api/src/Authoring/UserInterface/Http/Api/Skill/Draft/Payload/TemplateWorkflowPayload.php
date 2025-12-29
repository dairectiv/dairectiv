<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\UserInterface\Http\Api\Skill\Draft\Payload;

use Symfony\Component\Validator\Constraints;

final class TemplateWorkflowPayload extends WorkflowPayload
{
    /**
     * @param list<TemplatePayload> $templates
     */
    public function __construct(
        #[Constraints\Valid]
        public array $templates = [],
    ) {
    }

    public function toArray(): array
    {
        return [
            'templates' => array_map(
                static fn (TemplatePayload $templatePayload): array => $templatePayload->toState(),
                $this->templates,
            ),
        ];
    }
}
