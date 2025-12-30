<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Application\Workflow\RemoveStep;

use Dairectiv\Authoring\Domain\Object\Directive\DirectiveId;
use Dairectiv\Authoring\Domain\Object\Workflow\Step\StepId;
use Dairectiv\Authoring\Domain\Object\Workflow\Workflow;
use Dairectiv\Authoring\Domain\Repository\DirectiveRepository;
use Dairectiv\SharedKernel\Application\Command\CommandHandler;
use Dairectiv\SharedKernel\Domain\Object\Assert;

final readonly class Handler implements CommandHandler
{
    public function __construct(private DirectiveRepository $directiveRepository)
    {
    }

    public function __invoke(Input $input): void
    {
        $workflowId = DirectiveId::fromString($input->workflowId);
        $workflow = $this->directiveRepository->getDirectiveById($workflowId);

        \assert($workflow instanceof Workflow);

        $stepId = StepId::fromString($input->stepId);
        $step = $workflow->steps->filter(
            static fn ($s) => $s->id->equals($stepId),
        )->first();

        Assert::notFalse($step, \sprintf('Step with ID "%s" not found.', $input->stepId));

        $workflow->removeStep($step);

        $this->directiveRepository->save($workflow);
    }
}
