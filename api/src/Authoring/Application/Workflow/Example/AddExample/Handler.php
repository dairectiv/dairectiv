<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Application\Workflow\Example\AddExample;

use Dairectiv\Authoring\Domain\Object\Directive\DirectiveId;
use Dairectiv\Authoring\Domain\Object\Workflow\Example\Example;
use Dairectiv\Authoring\Domain\Repository\WorkflowRepository;
use Dairectiv\SharedKernel\Application\Command\CommandHandler;

final readonly class Handler implements CommandHandler
{
    public function __construct(private WorkflowRepository $workflowRepository)
    {
    }

    public function __invoke(Input $input): Output
    {
        $id = DirectiveId::fromString($input->workflowId);
        $workflow = $this->workflowRepository->getWorkflowById($id);

        $example = Example::create(
            $workflow,
            $input->scenario,
            $input->input,
            $input->output,
            $input->explanation,
        );

        return new Output($example);
    }
}
