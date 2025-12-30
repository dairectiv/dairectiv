<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Application\Skill\MoveStep;

use Dairectiv\Authoring\Domain\Object\Directive\DirectiveId;
use Dairectiv\Authoring\Domain\Object\Skill\Skill;
use Dairectiv\Authoring\Domain\Object\Skill\Workflow\StepId;
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
        $skillId = DirectiveId::fromString($input->skillId);
        $skill = $this->directiveRepository->getDirectiveById($skillId);

        \assert($skill instanceof Skill);

        $stepId = StepId::fromString($input->stepId);
        $step = $skill->steps->filter(
            static fn ($s) => $s->id->equals($stepId),
        )->first();

        Assert::notFalse($step, \sprintf('Step with ID "%s" not found.', $input->stepId));

        $afterStep = null;
        if (null !== $input->afterStepId) {
            $afterStepId = StepId::fromString($input->afterStepId);
            $afterStep = $skill->steps->filter(
                static fn ($s) => $s->id->equals($afterStepId),
            )->first();

            Assert::notFalse($afterStep, \sprintf('Reference step with ID "%s" not found.', $input->afterStepId));
        }

        $skill->moveStepAfter($step, $afterStep);

        $this->directiveRepository->save($skill);
    }
}
