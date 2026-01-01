<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Application\Workflow\GetWorkflow;

use Dairectiv\Authoring\Domain\Object\Directive\DirectiveId;
use Dairectiv\Authoring\Domain\Repository\WorkflowRepository;
use Dairectiv\SharedKernel\Application\Query\QueryHandler;

final readonly class Handler implements QueryHandler
{
    public function __construct(private WorkflowRepository $workflowRepository)
    {
    }

    public function __invoke(Input $input): Output
    {
        $id = DirectiveId::fromString($input->id);
        $workflow = $this->workflowRepository->getWorkflowById($id);

        return new Output($workflow);
    }
}
