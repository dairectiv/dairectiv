<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Application\Workflow\Step\RemoveStep;

use Dairectiv\Authoring\Domain\Object\Directive\DirectiveId;
use Dairectiv\Authoring\Domain\Object\Workflow\Step\Step;
use Dairectiv\Authoring\Domain\Object\Workflow\Step\StepId;
use Dairectiv\Authoring\Domain\Repository\WorkflowRepository;
use Dairectiv\SharedKernel\Application\Command\CommandHandler;
use Dairectiv\SharedKernel\Domain\Object\Assert;

final readonly class Handler implements CommandHandler
{
    public function __construct(private WorkflowRepository $workflowRepository)
    {
    }

    public function __invoke(Input $input): void
    {
        $workflowId = DirectiveId::fromString($input->workflowId);
        $workflow = $this->workflowRepository->getWorkflowById($workflowId);

        $stepId = StepId::fromString($input->stepId);
        $step = $workflow->steps->findFirst(
            static fn (int $key, Step $s) => $s->id->equals($stepId),
        );

        Assert::notNull($step, \sprintf('Step with ID "%s" not found.', $input->stepId));

        $workflow->removeStep($step);
    }
}
