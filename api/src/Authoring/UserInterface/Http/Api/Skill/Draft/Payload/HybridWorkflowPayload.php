<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\UserInterface\Http\Api\Skill\Draft\Payload;

use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

final class HybridWorkflowPayload extends WorkflowPayload
{
    /**
     * @param list<StepPayload> $steps
     * @param list<TemplatePayload> $templates
     */
    public function __construct(
        #[Constraints\Valid]
        #[Constraints\Count(min: 1)]
        public array $steps = [],

        #[Constraints\Valid]
        #[Constraints\Count(min: 1)]
        public array $templates = [],
    ) {
    }

    #[Constraints\Callback]
    public function assertStepsOrder(ExecutionContextInterface $context): void
    {
        $expectedStepsOrder = range(1, \count($this->steps));
        $stepsOrder = array_map(static fn (StepPayload $stepPayload): int => $stepPayload->order, $this->steps);

        if ($stepsOrder !== $expectedStepsOrder) {
            $context->buildViolation('The steps order is invalid.')
                ->atPath('steps')
                ->addViolation()
            ;
        }
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
