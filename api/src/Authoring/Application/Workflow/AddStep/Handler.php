<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Application\Workflow\AddStep;

use Dairectiv\Authoring\Domain\Object\Directive\DirectiveId;
use Dairectiv\Authoring\Domain\Object\Workflow\Step\Step;
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

    public function __invoke(Input $input): Output
    {
        $workflowId = DirectiveId::fromString($input->workflowId);
        $workflow = $this->directiveRepository->getDirectiveById($workflowId);

        \assert($workflow instanceof Workflow);

        $afterStep = null;
        if (null !== $input->afterStepId) {
            $afterStepId = StepId::fromString($input->afterStepId);
            $afterStep = $workflow->steps->filter(
                static fn ($s) => $s->id->equals($afterStepId),
            )->first();

            Assert::notFalse($afterStep, \sprintf('Step with ID "%s" not found.', $input->afterStepId));
        }

        $step = Step::create($workflow, $input->content, $afterStep);

        $this->directiveRepository->save($workflow);

        return new Output($step);
    }
}
